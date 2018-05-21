<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Files;
use Friday\Core\Options;
use Gumlet\ImageResize;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Xnova\Helpers;
use Friday\Core\Lang;
use PHPMailer\PHPMailer\PHPMailer;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Models\UserInfo;
use Xnova\User;
use Xnova\Queue;
use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/options")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class OptionsController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('options', 'xnova');

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

				$check = $this->db->query("SELECT user_id FROM game_users_auth WHERE external_id = '".$identity."'")->fetch();

				if (!isset($check['user_id']))
					$this->db->insertAsDict('game_users_auth', ['user_id' => $this->user->getId(), 'external_id' => $identity, 'create_time' => time()]);
				else
					throw new RedirectException('Данная точка входа уже используется', 'Ошибка', '/options/');
			}
			else
				throw new RedirectException('Ошибка получения данных', 'Ошибка', '/options/');
		}

		$this->response->redirect('options/');
	}

	public function emailAction ()
	{
		$userInfo = UserInfo::findFirst($this->user->id);

		if ($this->request->hasPost('password') && $this->request->hasPost('email'))
		{
			if (md5($this->request->getPost('password')) != $userInfo->password)
				throw new RedirectException('Heпpaвильный тeкyщий пapoль', 'Hacтpoйки', '/options/email/', 3);

			$email = $this->db->query("SELECT user_id FROM game_log_email WHERE user_id = " . $this->user->id . " AND ok = 0;")->fetch();

			if (isset($email['user_id']))
				throw new RedirectException('Заявка была отправлена ранее и ожидает модерации.', 'Hacтpoйки', '/options/', 3);

			$email = $this->db->query("SELECT id FROM game_users_info WHERE email = '" . addslashes(htmlspecialchars(trim($this->request->getPost('email')))) . "';")->fetch();

			if (isset($email['id']))
				throw new RedirectException('Данный email уже используется в игре.', 'Hacтpoйки', '/options/', 3);

			$this->db->query("INSERT INTO game_log_email VALUES (" . $this->user->id . ", " . time() . ", '" . addslashes(htmlspecialchars($this->request->getPost('email'))) . "', 0);");

			User::sendMessage(1, false, time(), 4, $this->user->username, 'Поступила заявка на смену Email от '.$this->user->username.' на '.addslashes(htmlspecialchars($this->request->getPost('email'))).'. <a href="'.$this->url->getStatic('admin/email/').'">Сменить</a>');

			throw new RedirectException('Заявка отправлена на рассмотрение', 'Hacтpoйки', '/options/', 3);
		}

		$this->tag->setTitle('Hacтpoйки');
	}

	public function changeAction ()
	{
		if ($this->request->hasPost('ld') && $this->request->getPost('ld') != '')
			$this->ld();

		$userInfo = UserInfo::findFirst($this->user->id);

		if ($this->request->hasPost('username') && trim($this->request->getPost('username')) != '' && trim($this->request->getPost('username')) != $this->user->username && mb_strlen(trim($this->request->getPost('username')), 'UTF-8') > 3)
		{
			$username = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", trim($this->request->getPost('username')));

			if (preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $username))
				$username = addslashes($username);
			else
				$username = $this->user->username;
		}
		else
			$username = $this->user->username;

		if ($this->request->hasPost('email') && !is_email($userInfo->email) && is_email($this->request->getPost('email')))
		{
			$e = addslashes(htmlspecialchars(trim($this->request->getPost('email'))));

			$email = $this->db->query("SELECT id FROM game_users_info WHERE email = '" . $e . "';")->fetch();

			if (!isset($email['id']))
			{
				$password = Helpers::randomSequence();

				$this->db->updateAsDict('game_users_info', ['email' => $e, 'password' => md5($password)], 'id = '.$this->user->getId());

				$mail = new PHPMailer();

				$mail->isMail();
				$mail->isHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
				$mail->addAddress($e, Options::get('site_title'));
				$mail->Subject = 'Пароль в Xnova Game: '.$this->config->game->universe.' вселенная';
				$mail->Body = "Ваш пароль от игрового аккаунта '" . $this->user->username . "': " . $password;
				$mail->send();

				throw new ErrorException('Ваш пароль от аккаунта: '.$password.'. Обязательно смените его на другой в настройках игры. Копия пароля отправлена на указанный вами электронный почтовый ящик.', 'Предупреждение');
			}
			else
				throw new RedirectException('Данный email уже используется в игре.', 'Hacтpoйки', '/options/', 3);
		}

		if ($this->user->vacation > time())
			$vacation = $this->user->vacation;
		else
		{
			$vacation = 0;

			if ($this->request->hasPost('vacation'))
			{
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

				if ($queueCount > 0)
					throw new RedirectException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: '.$queueCount.' объектов.', "Oшибкa", "/overview/", 5);
				elseif ($UserFlyingFleets > 0)
					throw new RedirectException('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "/overview/", 5);
				else
				{
					if ($this->user->vacation == 0)
						$vacation = time() + $this->config->game->get('vocationModeTime', 172800);
					else
						$vacation = $this->user->vacation;

					$buildsId = [4, 12, 212];

					foreach (Vars::getResources() AS $res)
						$buildsId[] = $this->registry->resource_flip[$res.'_mine'];

					$this->db->updateAsDict('game_planets_buildings', [
						'power' => 0
					], 'planet_id IN ('.implode(',', User::getPlanetsId($this->user->id)).') AND build_id IN ('.implode(',', $buildsId).')');

					$this->db->updateAsDict('game_planets_units', [
						'power' => 0
					], 'planet_id IN ('.implode(',', User::getPlanetsId($this->user->id)).') AND unit_id IN ('.implode(',', $buildsId).')');
				}
			}
		}

		$Del_Time = $this->request->hasPost('delete') ? (time() + 604800) : 0;

		if (!$this->user->isVacation())
		{
			$sex = ($this->request->getPost('sex', 'string', 'M') == 'F') ? 2 : 1;

			$color = $this->request->getPost('color', 'int', 1);
			$color = max(1, min(13, $color));

			if ($color < 1 || $color > 13)
				$color = 1;

			$timezone = $this->request->getPost('timezone', 'int', 0);

			if ($timezone < -32 || $timezone > 16)
				$timezone = 0;

			$SetSort = $this->request->getPost('settings_sort', 'int', 0);
			$SetOrder = $this->request->getPost('settings_order', 'int', 0);
			$about = Format::text($this->request->getPost('text', 'string', ''));
			$spy = $this->request->getPost('spy', 'int', 1);

			if ($spy < 1 || $spy > 1000)
				$spy = 1;


			$this->user->sex = $sex;
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

			$this->user->update();

			$settings = $userInfo->getSettings();

			$settings['records'] 		= $this->request->hasPost('records');
			$settings['bb_parser'] 		= $this->request->hasPost('bbcode');
			$settings['chatbox'] 		= $this->request->hasPost('chatbox');
			$settings['planetlist']		= $this->request->hasPost('planetlist');
			$settings['planetlistselect']= $this->request->hasPost('planetlistselect');
			$settings['only_available']	= $this->request->hasPost('available');

			$settings['planet_sort'] 	= (int) $SetSort;
			$settings['planet_sort_order'] = (int) $SetOrder;
			$settings['color'] 			= (int) $color;
			$settings['timezone'] 		= (int) $timezone;
			$settings['spy'] 			= (int) $spy;

			$userInfo->setSettings($settings);

			if ($this->request->hasFiles())
			{
				/** @var $files \Phalcon\Http\Request\File[] */
				$files = $this->request->getUploadedFiles();

				foreach ($files as $file)
				{
					if ($file->isUploadedFile() && $file->getKey() == 'image')
					{
						$fileType = $file->getRealType();

						if (strpos($fileType, 'image/') === false)
							throw new ErrorException('Разрешены к загрузке только изображения');

						if ($userInfo->image > 0)
							Files::delete($userInfo->image);

						$userInfo->image = Files::save($file);

						$f = Files::getById($userInfo->image);

						if ($f)
						{
							$image = new ImageResize($this->request->getServer('DOCUMENT_ROOT').$f['src']);
							$image->quality_jpg = 90;
							$image->crop(300, 300, ImageResize::CROPCENTER);
							$image->save($this->request->getServer('DOCUMENT_ROOT').$f['src']);
						}
					}
				}
			}

			if ($this->request->getPost('image_delete'))
			{
				if (Files::delete($userInfo->image))
					$userInfo->image = 0;
			}

			$userInfo->about = $about;
			$userInfo->update();

			$this->session->remove('config');
			$this->cache->delete('app::planetlist_'.$this->user->getId());
		}
		else
		{
			$this->user->vacation = $vacation;
			$this->user->deltime = $Del_Time;

			$this->user->update();
		}

		if ($this->request->hasPost('password') && $this->request->getPost('password') != '' && $this->request->getPost('new_password') != '')
		{
			if (md5($this->request->getPost('password')) != $userInfo->password)
				throw new RedirectException('Heпpaвильный тeкyщий пapoль', 'Cмeнa пapoля', '/options/', 3);

			if ($this->request->getPost('new_password') != $this->request->getPost('new_password_confirm'))
				throw new RedirectException('Bвeдeнныe пapoли нe coвпaдaют', 'Cмeнa пapoля', '/options/', 3);

			$userInfo->password = md5($this->request->getPost('new_password'));
			$userInfo->update();

			$this->auth->remove(false);

			throw new RedirectException('Уcпeшнo', 'Cмeнa пapoля', '/', 2);
		}

		if ($this->user->username != $username)
		{
			if ($userInfo->username_last > (time() - 86400))
				throw new RedirectException('Смена игрового имени возможна лишь раз в сутки.', 'Cмeнa имeни', '/options/', 3);

			$query = $this->db->query("SELECT id FROM game_users WHERE username = '" . $username . "'");

			if ($query->numRows())
				throw new RedirectException('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe', 'Cмeнa имeни', '/options/', 3);

			if (!preg_match("/^[a-zA-Za-яA-Я0-9_\.\,\-\!\?\*\ ]+$/u", $username) || mb_strlen($username, 'UTF-8') < 5)
				throw new RedirectException('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы', 'Cмeнa имeни', '/options/', 3);

			$this->user->username = $username;
			$this->user->update();

			$userInfo->username_last = time();
			$userInfo->update();

			$this->db->query("INSERT INTO game_log_username VALUES (" . $this->user->id . ", " . time() . ", '" . $username . "');");

			throw new RedirectException('Уcпeшнo', 'Cмeнa имeни', '/', 2);
		}

		throw new RedirectException(_getText('succeful_save'), "Hacтpoйки игpы", '/options/', 3);
	}

	private function ld ()
	{
		if (!$this->request->hasPost('ld') || $this->request->getPost('ld') == '')
			throw new RedirectException('Ввведите текст сообщения', 'Ошибка', '/options/', 3);

		$this->db->query("INSERT INTO game_private (u_id, text, time) VALUES (" . $this->user->id . ", '" . addslashes(htmlspecialchars($this->request->getPost('ld'))) . "', " . time() . ")");

		throw new RedirectException('Запись добавлена в личное дело', 'Успешно', '/options/', 3);
	}
	
	public function indexAction ()
	{
		$userInfo = UserInfo::findFirst($this->user->id);

		$parse = [];

		if ($this->user->vacation > 0)
		{
			$parse['um_end_date'] = $this->game->datezone("d.m.Y H:i:s", $this->user->vacation);
			$parse['opt_delac_data'] = ($this->user->deltime > 0) ? " checked" : '';
			$parse['opt_modev_data'] = ($this->user->vacation > 0) ? " checked" : '';
			$parse['opt_usern_data'] = $this->user->username;

			$this->view->pick('options/vacation');
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

			$parse['opt_usern_datatime'] = $userInfo->username_last;
			$parse['opt_usern_data'] = $this->user->username;
			$parse['opt_mail_data'] = $userInfo->email;

			$parse['opt_record_data'] = $this->user->getUserOption('records') ? " checked" : '';
			$parse['opt_bbcode_data'] = $this->user->getUserOption('bb_parser') ? ' checked' : '';
			$parse['opt_chatbox_data'] = $this->user->getUserOption('chatbox') ? " checked" : '';
			$parse['opt_planetlist_data'] = $this->user->getUserOption('planetlist') ? " checked" : '';
			$parse['opt_planetlistselect_data'] = $this->user->getUserOption('planetlistselect') ? " checked" : '';
			$parse['opt_available_data'] = $this->user->getUserOption('only_available') ? " checked" : '';
			$parse['opt_delac_data'] = $this->user->deltime > 0 ? " checked" : '';
			$parse['opt_modev_data'] = $this->user->vacation > 0 ? " checked" : '';

			$parse['sex'] = $this->user->sex;
			$parse['about'] = $userInfo->about;
			$parse['timezone'] = isset($settings['timezone']) ? $settings['timezone'] : 0;
			$parse['spy'] = isset($settings['spy']) ? $settings['spy'] : 1;
			$parse['color'] = isset($settings['color']) ? $settings['color'] : 0;

			$parse['auth'] = $this->db->extractResult($this->db->query("SELECT * FROM game_users_auth WHERE user_id = ".$this->user->getId().""));

			$parse['bot_auth'] = $this->db->fetchOne('SELECT * FROM bot_requests WHERE user_id = '.$this->user->getId().'');
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Hacтpoйки');
	}
}