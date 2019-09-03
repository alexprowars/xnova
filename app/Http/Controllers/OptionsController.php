<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Gumlet\ImageResize;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Files;
use Xnova\Format;
use Xnova\Game;
use Xnova\Helpers;
use Xnova\Mail\UserLostPasswordSuccess;
use Xnova\Models\Fleet;
use Xnova\Models\UserInfo;
use Xnova\User;
use Xnova\Queue;
use Xnova\Controller;
use Xnova\Vars;

class OptionsController extends Controller
{
	public function __construct ()
	{
		parent::__construct();

		$this->showTopPanel(false);
	}

	public function externalAction ()
	{
		if (isset($_REQUEST['token']) && $_REQUEST['token'] != '')
		{
			$s = file_get_contents('http://u-login.com/token.php?token=' . $_REQUEST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
			$data = json_decode($s, true);

			if (isset($data['identity']))
			{
				$identity = isset($data['profile']) && $data['profile'] != '' ? $data['profile'] : $data['identity'];

				$check = $this->db->query("SELECT user_id FROM users_auth WHERE external_id = '".$identity."'")->fetch();

				if (!isset($check['user_id']))
					DB::table('users_auth')->insert(['user_id' => $this->user->getId(), 'external_id' => $identity, 'create_time' => time()]);
				else
					throw new RedirectException('Данная точка входа уже используется', '/options/');
			}
			else
				throw new RedirectException('Ошибка получения данных', '/options/');
		}

		throw new RedirectException('', '/options/');
	}

	public function emailAction ()
	{
		$userInfo = UserInfo::findFirst($this->user->id);

		if (Input::post('password') && Input::post('email'))
		{
			if (md5(Input::post('password')) != $userInfo->password)
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');

			$email = $this->db->query("SELECT user_id FROM log_email WHERE user_id = " . $this->user->id . " AND ok = 0;")->fetch();

			if (isset($email['user_id']))
				throw new ErrorException('Заявка была отправлена ранее и ожидает модерации.');

			$email = $this->db->query("SELECT id FROM users_info WHERE email = '" . addslashes(htmlspecialchars(trim(Input::post('email')))) . "';")->fetch();

			if (isset($email['id']))
				throw new ErrorException('Данный email уже используется в игре.');

			$this->db->query("INSERT INTO log_email VALUES (" . $this->user->id . ", " . time() . ", '" . addslashes(htmlspecialchars(Input::post('email'))) . "', 0);");

			User::sendMessage(1, false, time(), 4, $this->user->username, 'Поступила заявка на смену Email от '.$this->user->username.' на '.addslashes(htmlspecialchars(Input::post('email'))).'. <a href="'.$this->url->getStatic('admin/email/').'">Сменить</a>');

			throw new RedirectException('Заявка отправлена на рассмотрение', '/options/');
		}

		$this->setTitle('Hacтpoйки');
	}

	public function changeAction ()
	{
		if (Input::post('ld') && Input::post('ld') != '')
			$this->ld();

		$userInfo = UserInfo::findFirst($this->user->id);

		if (Input::post('username')
			&& trim(Input::post('username')) != ''
			&& trim(Input::post('username')) != $this->user->username
			&& mb_strlen(trim(Input::post('username')), 'UTF-8') > 3
		)
		{
			$username = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", trim(Input::post('username')));

			if (preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $username))
				$username = addslashes($username);
			else
				$username = $this->user->username;
		}
		else
			$username = $this->user->username;

		if (Input::post('email') && !is_email($userInfo->email) && is_email(Input::post('email')))
		{
			$e = addslashes(htmlspecialchars(trim(Input::post('email'))));

			$email = $this->db->query("SELECT id FROM users_info WHERE email = '" . $e . "';")->fetch();

			if (isset($email['id']))
				throw new ErrorException('Данный email уже используется в игре.');

			$password = Helpers::randomSequence();

			$this->db->updateAsDict('users_info', [
				'email' => $e,
				'password' => md5($password)
			], 'id = '.$this->user->getId());

			Mail::to($e)->send(new UserLostPasswordSuccess([
				'#EMAIL#' => $e,
				'#PASSWORD#' => $password,
			]));

			throw new ErrorException('Ваш пароль от аккаунта: '.$password.'. Обязательно смените его на другой в настройках игры. Копия пароля отправлена на указанный вами электронный почтовый ящик.');
		}

		if ($this->user->vacation > time())
			$vacation = $this->user->vacation;
		else
		{
			$vacation = 0;

			if (Input::post('vacation'))
			{
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

				if ($queueCount > 0)
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: '.$queueCount.' объектов.');
				elseif ($UserFlyingFleets > 0)
					throw new ErrorException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
				else
				{
					if ($this->user->vacation == 0)
						$vacation = time() + Config::get('game.vocationModeTime', 172800);
					else
						$vacation = $this->user->vacation;

					$buildsId = [4, 12, 212];

					foreach (Vars::getResources() AS $res)
						$buildsId[] = Vars::getIdByName($res.'_mine');

					$this->db->updateAsDict('planets_buildings', [
						'power' => 0
					], 'planet_id IN ('.implode(',', User::getPlanetsId($this->user->id)).') AND build_id IN ('.implode(',', $buildsId).')');

					$this->db->updateAsDict('planets_units', [
						'power' => 0
					], 'planet_id IN ('.implode(',', User::getPlanetsId($this->user->id)).') AND unit_id IN ('.implode(',', $buildsId).')');
				}
			}
		}

		$Del_Time = Input::post('delete') ? (time() + 604800) : 0;

		if (!$this->user->isVacation())
		{
			$sex = (Input::post('sex', 'M') == 'F') ? 2 : 1;

			$color = Input::post('color', 1);
			$color = max(1, min(13, $color));

			if ($color < 1 || $color > 13)
				$color = 1;

			$timezone = Input::post('timezone', 0);

			if ($timezone < -32 || $timezone > 16)
				$timezone = 0;

			$SetSort = Input::post('settings_sort', 0);
			$SetOrder = Input::post('settings_order', 0);
			$about = Format::text(Input::post('text', ''));
			$spy = Input::post('spy', 1);

			if ($spy < 1 || $spy > 1000)
				$spy = 1;


			$this->user->sex = $sex;
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

			$this->user->update();

			$settings = $userInfo->getSettings();

			$settings['records'] 		= Input::post('records');
			$settings['bb_parser'] 		= Input::post('bbcode');
			$settings['chatbox'] 		= Input::post('chatbox');
			$settings['planetlist']		= Input::post('planetlist');
			$settings['planetlistselect']= Input::post('planetlistselect');
			$settings['only_available']	= Input::post('available');

			$settings['planet_sort'] 	= (int) $SetSort;
			$settings['planet_sort_order'] = (int) $SetOrder;
			$settings['color'] 			= (int) $color;
			$settings['timezone'] 		= (int) $timezone;
			$settings['spy'] 			= (int) $spy;

			$userInfo->setSettings($settings);

			if (Request::instance()->hasFile('image'))
			{
				$file = Request::instance()->file('image');

				if ($file->isValid())
				{
					$fileType = $file->getMimeType();

					if (strpos($fileType, 'image/') === false)
						throw new ErrorException('Разрешены к загрузке только изображения');

					if ($userInfo->image > 0)
						Files::delete($userInfo->image);

					$userInfo->image = Files::save($file);

					$f = Files::getById($userInfo->image);

					if ($f)
					{
						$image = new ImageResize($f['path']);
						$image->quality_jpg = 90;
						$image->crop(300, 300, ImageResize::CROPCENTER);
						$image->save($f['path']);
					}
				}
			}

			if (Input::post('image_delete'))
			{
				if (Files::delete($userInfo->image))
					$userInfo->image = 0;
			}

			$userInfo->about = $about;
			$userInfo->update();

			Session::remove('config');
			Cache::forget('app::planetlist_'.$this->user->getId());
		}
		else
		{
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

			$this->user->update();
		}

		if (Input::post('password')
			&& Input::post('password') != ''
			&& Input::post('new_password') != ''
		)
		{
			if (md5(Input::post('password')) != $userInfo->password)
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');

			if (Input::post('new_password') != Input::post('new_password_confirm'))
				throw new ErrorException('Bвeдeнныe пapoли нe coвпaдaют');

			$userInfo->password = md5(Input::post('new_password'));
			$userInfo->update();

			$this->auth->remove(false);

			throw new RedirectException('Пароль успешно изменён', '/');
		}

		if ($this->user->username != $username)
		{
			if ($userInfo->username_last > (time() - 86400))
				throw new ErrorException('Смена игрового имени возможна лишь раз в сутки.');

			$query = $this->db->query("SELECT id FROM users WHERE username = '" . $username . "'");

			if ($query->numRows())
				throw new ErrorException('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe');

			if (!preg_match("/^[a-zA-Za-яA-Я0-9_\.\,\-\!\?\*\ ]+$/u", $username) || mb_strlen($username, 'UTF-8') < 5)
				throw new ErrorException('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы');

			$this->user->username = $username;
			$this->user->update();

			$userInfo->username_last = time();
			$userInfo->update();

			$this->db->query("INSERT INTO log_username VALUES (" . $this->user->id . ", " . time() . ", '" . $username . "');");

			throw new RedirectException('Имя пользователя изменено', '/options/');
		}

		throw new RedirectException(__('options.succeful_save'), '/options/');
	}

	private function ld ()
	{
		if (!Input::post('ld') || Input::post('ld') == '')
			throw new ErrorException('Ввведите текст сообщения');

		$this->db->query("INSERT INTO private (u_id, text, time) VALUES (" . $this->user->id . ", '" . addslashes(htmlspecialchars(Input::post('ld'))) . "', " . time() . ")");

		throw new RedirectException('Запись добавлена в личное дело', '/options/');
	}

	public function index ()
	{
		$userInfo = UserInfo::findFirst($this->user->id);

		$parse = [];
		$parse['social'] = Config::get('game.view.socialIframeView', 0) > 0;
		$parse['vacation'] = $this->user->vacation > 0;

		if ($this->user->vacation > 0)
		{
			$parse['um_end_date'] = Game::datezone("d.m.Y H:i:s", $this->user->vacation);
			$parse['opt_delac_data'] = ($this->user->deltime > 0);
			$parse['opt_modev_data'] = ($this->user->vacation > 0);
			$parse['opt_usern_data'] = $this->user->username;
		}
		else
		{
			$settings = $userInfo->getSettings();

			$parse['settings'] = $settings;

			$parse['avatar'] = '';

			if ($userInfo->image > 0)
			{
				$file = Files::getById($userInfo->image);

				if ($file)
					$parse['avatar'] = $file['src'];
			}

			$this->user->setOptions($settings);

			$parse['opt_usern_datatime'] = $userInfo->username_last < (time() - 86400);
			$parse['opt_usern_data'] = $this->user->username;
			$parse['opt_mail_data'] = $userInfo->email;
			$parse['opt_isemail'] = is_email($userInfo->email);

			$parse['opt_record_data'] = $this->user->getUserOption('records');
			$parse['opt_bbcode_data'] = $this->user->getUserOption('bb_parser');
			$parse['opt_chatbox_data'] = $this->user->getUserOption('chatbox');
			$parse['opt_planetlist_data'] = $this->user->getUserOption('planetlist');
			$parse['opt_planetlistselect_data'] = $this->user->getUserOption('planetlistselect');
			$parse['opt_available_data'] = $this->user->getUserOption('only_available');
			$parse['opt_delac_data'] = $this->user->deltime > 0;
			$parse['opt_modev_data'] = $this->user->vacation > 0;

			$parse['sex'] = $this->user->sex;
			$parse['about'] = preg_replace('!<br.*>!iU', "\n", $userInfo->about);
			$parse['timezone'] = isset($settings['timezone']) ? $settings['timezone'] : 0;
			$parse['spy'] = isset($settings['spy']) ? $settings['spy'] : 1;
			$parse['color'] = isset($settings['color']) ? $settings['color'] : 0;

			$parse['auth'] = $this->db->extractResult($this->db->query("SELECT * FROM users_auth WHERE user_id = ".$this->user->getId().""));

			$parse['bot_auth'] = $this->db->fetchOne('SELECT * FROM bot_requests WHERE user_id = '.$this->user->getId().'');
		}

		$this->setTitle('Hacтpoйки');

		return $parse;
	}
}