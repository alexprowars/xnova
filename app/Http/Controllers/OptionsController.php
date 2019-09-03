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
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Files;
use Xnova\Format;
use Xnova\Game;
use Xnova\Helpers;
use Xnova\Mail\UserLostPasswordSuccess;
use Xnova\Models\Fleet;
use Xnova\Models;
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

				$check = DB::selectOne("SELECT user_id FROM users_auth WHERE external_id = '".$identity."'");

				if (!$check)
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
		/** @var Models\UsersInfo $userInfo */
		$userInfo = Models\UsersInfo::query()->find($this->user->id);

		if (Request::post('password') && Request::post('email'))
		{
			if (md5(Request::post('password')) != $userInfo->password)
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');

			$email = DB::selectOne("SELECT user_id FROM log_email WHERE user_id = " . $this->user->id . " AND ok = 0;");

			if ($email)
				throw new ErrorException('Заявка была отправлена ранее и ожидает модерации.');

			$email = DB::selectOne("SELECT id FROM users_info WHERE email = '" . addslashes(htmlspecialchars(trim(Request::post('email')))) . "';");

			if ($email)
				throw new ErrorException('Данный email уже используется в игре.');

			DB::statement("INSERT INTO log_email VALUES (" . $this->user->id . ", " . time() . ", '" . addslashes(htmlspecialchars(Request::post('email'))) . "', 0);");

			User::sendMessage(1, false, time(), 4, $this->user->username, 'Поступила заявка на смену Email от '.$this->user->username.' на '.addslashes(htmlspecialchars(Request::post('email'))).'. <a href="'.URL::to('admin/email/').'">Сменить</a>');

			throw new RedirectException('Заявка отправлена на рассмотрение', '/options/');
		}

		$this->setTitle('Hacтpoйки');
	}

	public function changeAction ()
	{
		if (Request::post('ld') && Request::post('ld') != '')
			$this->ld();

		/** @var Models\UsersInfo $userInfo */
		$userInfo = Models\UsersInfo::query()->find($this->user->id);

		if (Request::post('username')
			&& trim(Request::post('username')) != ''
			&& trim(Request::post('username')) != $this->user->username
			&& mb_strlen(trim(Request::post('username')), 'UTF-8') > 3
		)
		{
			$username = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", trim(Request::post('username')));

			if (preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $username))
				$username = addslashes($username);
			else
				$username = $this->user->username;
		}
		else
			$username = $this->user->username;

		if (Request::post('email') && !is_email($userInfo->email) && is_email(Request::post('email')))
		{
			$e = addslashes(htmlspecialchars(trim(Request::post('email'))));

			$email = DB::selectOne("SELECT id FROM users_info WHERE email = '" . $e . "'");

			if ($email)
				throw new ErrorException('Данный email уже используется в игре.');

			$password = Helpers::randomSequence();

			Models\UsersInfo::query()->where('id', $this->user->getId())
				->update([
					'email' => $e,
					'password' => md5($password)
				]);

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

			if (Request::post('vacation'))
			{
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Models\Fleet::query()->where('owner', $this->user->id)->count();

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

					Models\PlanetsBuildings::query()->whereIn('planet_id', User::getPlanetsId($this->user->id))
						->whereIn('build_id', $buildsId)
						->update(['power' => 0]);

					Models\PlanetsUnits::query()->whereIn('planet_id', User::getPlanetsId($this->user->id))
						->whereIn('unit_id', $buildsId)
						->update(['power' => 0]);
				}
			}
		}

		$Del_Time = Request::post('delete') ? (time() + 604800) : 0;

		if (!$this->user->isVacation())
		{
			$sex = (Request::post('sex', 'M') == 'F') ? 2 : 1;

			$color = Request::post('color', 1);
			$color = max(1, min(13, $color));

			if ($color < 1 || $color > 13)
				$color = 1;

			$timezone = Request::post('timezone', 0);

			if ($timezone < -32 || $timezone > 16)
				$timezone = 0;

			$SetSort = Request::post('settings_sort', 0);
			$SetOrder = Request::post('settings_order', 0);
			$about = Format::text(Request::post('text', ''));
			$spy = Request::post('spy', 1);

			if ($spy < 1 || $spy > 1000)
				$spy = 1;


			$this->user->sex = $sex;
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

			$this->user->update();

			$settings = $userInfo->getSettings();

			$settings['records'] 		= Request::post('records');
			$settings['bb_parser'] 		= Request::post('bbcode');
			$settings['chatbox'] 		= Request::post('chatbox');
			$settings['planetlist']		= Request::post('planetlist');
			$settings['planetlistselect']= Request::post('planetlistselect');
			$settings['only_available']	= Request::post('available');

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

			if (Request::post('image_delete'))
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

		if (Request::post('password')
			&& Request::post('password') != ''
			&& Request::post('new_password') != ''
		)
		{
			if (md5(Request::post('password')) != $userInfo->password)
				throw new ErrorException('Heпpaвильный тeкyщий пapoль');

			if (Request::post('new_password') != Request::post('new_password_confirm'))
				throw new ErrorException('Bвeдeнныe пapoли нe coвпaдaют');

			$userInfo->password = md5(Request::post('new_password'));
			$userInfo->update();

			$this->auth->remove(false);

			throw new RedirectException('Пароль успешно изменён', '/');
		}

		if ($this->user->username != $username)
		{
			if ($userInfo->username_last > (time() - 86400))
				throw new ErrorException('Смена игрового имени возможна лишь раз в сутки.');

			$query = DB::selectOne("SELECT id FROM users WHERE username = '" . $username . "'");

			if ($query)
				throw new ErrorException('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe');

			if (!preg_match("/^[a-zA-Za-яA-Я0-9_.,\-!?* ]+$/u", $username) || mb_strlen($username) < 5)
				throw new ErrorException('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы');

			$this->user->username = $username;
			$this->user->update();

			$userInfo->username_last = time();
			$userInfo->update();

			DB::statement("INSERT INTO log_username VALUES (" . $this->user->id . ", " . time() . ", '" . $username . "');");

			throw new RedirectException('Имя пользователя изменено', '/options/');
		}

		throw new RedirectException(__('options.succeful_save'), '/options/');
	}

	private function ld ()
	{
		if (!Request::post('ld') || Request::post('ld') == '')
			throw new ErrorException('Ввведите текст сообщения');

		DB::statement("INSERT INTO private (u_id, text, time) VALUES (" . $this->user->id . ", '" . addslashes(htmlspecialchars(Request::post('ld'))) . "', " . time() . ")");

		throw new RedirectException('Запись добавлена в личное дело', '/options/');
	}

	public function index ()
	{
		/** @var Models\UsersInfo $userInfo */
		$userInfo = Models\UsersInfo::query()->find($this->user->id);

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

			$authData = DB::select("SELECT * FROM users_auth WHERE user_id = ".$this->user->getId()."");

			$parse['auth'] = array_map(function ($value) {
			    return (array) $value;
			}, $authData);
		}

		$this->setTitle('Hacтpoйки');

		return $parse;
	}
}