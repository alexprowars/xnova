<?php

namespace App\Controllers;

use Xcms\core;
use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;
use Xnova\queueManager;

class OptionsController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		strings::includeLang('options');
	}

	public function external ()
	{
		if (isset($_REQUEST['token']) && $_REQUEST['token'] != '')
		{
			$s = file_get_contents('http://u-login.com/token.php?token=' . $_REQUEST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
			$data = json_decode($s, true);

			if (isset($data['identity']))
			{
				$check = db::query("SELECT user_id FROM game_users_auth WHERE external_id = '".$data['identity']."'", true);

				if (!isset($check['user_id']))
					sql::build()->insert('game_users_auth')->set(Array('user_id' => user::get()->getId(), 'external_id' => $data['identity'], 'register_time' => time()))->execute();
				else
					$this->message('Данная точка входа уже используется', 'Ошибка', '?set=options');
			}
			else
				$this->message('Ошибка получения данных', 'Ошибка', '?set=options');
		}

		request::redirectTo('?set=options');
	}

	public function changeemail ()
	{
		$inf = db::query("SELECT * FROM game_users_info WHERE id = " . user::get()->data['id'] . "", true);

		if (isset($_POST['db_password']) && isset($_POST['email']))
		{
			if (md5($_POST["db_password"]) != $inf["password"])
				$this->message('Heпpaвильный тeкyщий пapoль', 'Hacтpoйки', '?set=options&mode=changeemail', 3);
			else
			{
				$email = db::query("SELECT user_id FROM game_log_email WHERE user_id = " . user::get()->data['id'] . " AND ok = 0;", true);

				if (isset($email['user_id']))
				{
					$this->message('Заявка была отправлена ранее и ожидает модерации.', 'Hacтpoйки', '?set=options', 3);
				}
				else
				{
					$email = db::query("SELECT id FROM game_users_info WHERE email = '" . addslashes(htmlspecialchars(trim($_POST['email']))) . "';", true);

					if (!isset($email['id']))
					{
						db::query("INSERT INTO game_log_email VALUES (" . user::get()->data['id'] . ", " . time() . ", '" . addslashes(htmlspecialchars($_POST['email'])) . "', 0);");
						$this->message('Заявка отправлена на рассмотрение', 'Hacтpoйки', '?set=options', 3);
					}
					else
						$this->message('Данный email уже используется в игре.', 'Hacтpoйки', '?set=options', 3);
				}
			}
		}

		$this->setTemplate('options_email');

		$this->setTitle('Hacтpoйки');
		$this->showTopPanel(false);
		$this->display();
	}

	public function change ()
	{
		global $session;

		if (isset($_POST['ld']) && $_POST['ld'] != '')
		{
			$this->ld();
		}

		$inf = db::query("SELECT * FROM game_users_info WHERE id = " . user::get()->data['id'] . "", true);

		if (isset($_POST["db_character"]) && trim($_POST["db_character"]) != '' && trim($_POST["db_character"]) != user::get()->data['username'] && mb_strlen(trim($_POST["db_character"]), 'UTF-8') > 3)
		{
			$_POST["db_character"] = preg_replace("/([\s\x{0}\x{0B}]+)/iu", " ", trim($_POST["db_character"]));

			if (preg_match("/^[А-Яа-яЁёa-zA-Z0-9_\-\!\~\.@ ]+$/u", $_POST['db_character']))
				$username = addslashes($_POST['db_character']);
			else
				$username = user::get()->data['username'];
		}
		else
			$username = user::get()->data['username'];

		if (isset($_POST['email']) && !is_email($inf['email']) && is_email($_POST['email']))
		{
			$e = addslashes(htmlspecialchars(trim($_POST['email'])));

			$email = db::query("SELECT id FROM game_users_info WHERE email = '" . $e . "';", true);

			if (!isset($email['id']))
			{
				$password = strings::randomSequence();

				sql::build()->update('game_users_info')->setField('email', $e)->setField('password', md5($password))->where('id', '=', user::get()->getId())->execute();

				core::loadLib('mail');

				$mail = new \PHPMailer();

				$mail->IsMail();
				$mail->IsHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->SetFrom(ADMINEMAIL, SITE_TITLE);
				$mail->AddAddress($e, SITE_TITLE);
				$mail->Subject = 'Пароль в Xnova Game: '.UNIVERSE.' вселенная';
				$mail->Body = "Ваш пароль от игрового аккаунта '" . user::get()->data['username'] . "': " . $password;
				$mail->Send();

				$this->message('Ваш пароль от аккаунта: '.$password.'. Обязательно смените его на другой в настройках игры. Копия пароля отправлена на указанный вами электронный почтовый ящик.', 'Предупреждение');
			}
			else
				$this->message('Данный email уже используется в игре.', 'Hacтpoйки', '?set=options', 3);
		}

		if (user::get()->data['urlaubs_modus_time'] > time())
		{
			$urlaubs_modus_time = user::get()->data['urlaubs_modus_time'];
		}
		else
		{
			$urlaubs_modus_time = 0;

			if (isset($_POST["urlaubs_modus"]) && $_POST["urlaubs_modus"] == 'on')
			{
				$queueManager = new queueManager();
				$queueCount = 0;

				$BuildOnPlanets = db::query("SELECT `queue` FROM game_planets WHERE `id_owner` = '" . user::get()->data['id'] . "'");

				while ($BuildOnPlanet = db::fetch($BuildOnPlanets))
				{
					$queueManager->loadQueue($BuildOnPlanet['queue']);

					$queueCount += $queueManager->getCount();
				}

				$UserFlyingFleets = db::query("SELECT `fleet_id` FROM game_fleets WHERE `fleet_owner` = '" . user::get()->data['id'] . "'");

				if ($queueCount > 0)
					$this->message('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe. Строится: '.$queueCount.' объектов.', "Oшибкa", "?set=overview", 5);
				elseif (db::num_rows($UserFlyingFleets) > 0)
					$this->message('Heвoзмoжнo включить peжим oтпycкa. Для включeния y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "?set=overview", 5);
				else
				{
					if (user::get()->data['urlaubs_modus_time'] == 0)
						$urlaubs_modus_time = time() + core::getConfig('vocationModeTime', 172800);
					else
						$urlaubs_modus_time = user::get()->data['urlaubs_modus_time'];

					db::query("UPDATE game_planets SET `metal_mine_porcent` = '0', `crystal_mine_porcent` = '0', `deuterium_mine_porcent` = '0', `solar_plant_porcent` = '0', `fusion_plant_porcent` = '0', `solar_satelit_porcent` = '0' WHERE `id_owner` = '" . user::get()->data['id'] . "'");
				}
			}
		}

		$Del_Time = (isset($_POST["db_deaktjava"]) && $_POST["db_deaktjava"] == 'on') ? (time() + 604800) : 0;

		if (user::get()->data['urlaubs_modus_time'] == 0)
		{
			$sex = ($_POST['sex'] == 'F') ? 2 : 1;

			$color = intval($_POST['color']);
			if ($color < 1 || $color > 13)
				$color = 1;

			$timezone = intval($_POST['timezone']);
			if ($timezone < -32 || $timezone > 16)
				$timezone = 0;

			$SetSort = intval($_POST['settings_sort']);
			$SetOrder = intval($_POST['settings_order']);
			$about = strings::FormatText($_POST['text']);
			$spy = intval($_POST['spy']);

			if ($spy < 1 || $spy > 1000)
				$spy = 1;

			$options = user::get()->getUserOption();
			$options['records'] 		= (isset($_POST["records"]) && $_POST["records"] == 'on') ? 1 : 0;
			$options['security'] 		= (isset($_POST["security"]) && $_POST["security"] == 'on') ? 1 : 0;
			$options['bb_parser'] 		= (isset($_POST["bbcode"]) && $_POST["bbcode"] == 'on') ? 1 : 0;
			$options['ajax_navigation'] = (isset($_POST["ajaxnav"]) && $_POST["ajaxnav"] == 'on') ? 1 : 0;
			$options['gameactivity'] 	= (isset($_POST["gameactivity"]) && $_POST["gameactivity"] == 'on') ? 1 : 0;
			$options['planetlist']		= (isset($_POST["planetlist"]) && $_POST["planetlist"] == 'on') ? 1 : 0;
			$options['planetlistselect']= (isset($_POST["planetlistselect"]) && $_POST["planetlistselect"] == 'on') ? 1 : 0;
			$options['only_available']	= (isset($_POST["available"]) && $_POST["available"] == 'on') ? 1 : 0;

			db::query("UPDATE game_users SET options_toggle = '".user::get()->packOptions($options)."', sex = '" . $sex . "', `urlaubs_modus_time` = '" . $urlaubs_modus_time . "', `deltime` = '" . $Del_Time . "' WHERE `id` = '" . user::get()->data['id'] . "'");

			$ui_query = '';

			if ($SetSort != $inf['planet_sort'])
				$ui_query .= ", `planet_sort` = '" . $SetSort . "'";

			if ($SetOrder != $inf['planet_sort_order'])
				$ui_query .= ", `planet_sort_order` = '" . $SetOrder . "'";

			if ($color != $inf['color'])
				$ui_query .= ", `color` = '" . $color . "'";

			if ($timezone != $inf['timezone'])
				$ui_query .= ", `timezone` = '" . $timezone . "'";

			if ($about != $inf['about'])
				$ui_query .= ", `about` = '" . $about . "'";

			if ($spy != $inf['spy'])
				$ui_query .= ", `spy` = '" . $spy . "'";

			if ($ui_query != '')
			{
				if ($ui_query != '')
					$ui_query[0] = ' ';

				db::query("UPDATE game_users_info SET" . $ui_query . " WHERE `id` = '" . user::get()->data['id'] . "'");
			}

			unset($_SESSION['config']);
		}
		else
			db::query("UPDATE game_users SET `urlaubs_modus_time` = '" . $urlaubs_modus_time . "', `deltime` = '" . $Del_Time . "' WHERE `id` = '" . user::get()->data['id'] . "' LIMIT 1");

		if (isset($_POST["db_password"]) && $_POST["db_password"] != "" && $_POST["newpass1"] != "")
		{
			if (md5($_POST["db_password"]) != $inf["password"])
				$this->message('Heпpaвильный тeкyщий пapoль', 'Cмeнa пapoля', '?set=options', 3);
			elseif ($_POST["newpass1"] == $_POST["newpass2"])
			{
				$newpass = md5($_POST["newpass1"]);
				db::query("UPDATE game_users_info SET `password` = '" . $newpass . "' WHERE `id` = '" . user::get()->data['id'] . "' LIMIT 1");

				$session->ClearSession(false);

				$this->message('Уcпeшнo', 'Cмeнa пapoля', '?set=login', 2);
			}
			else
				$this->message('Bвeдeнныe пapoли нe coвпaдaют', 'Cмeнa пapoля', '?set=options', 3);
		}

		if (user::get()->data['username'] != $username)
		{
			if ($inf['username_last'] > (time() - 86400))
			{
				$this->message('Смена игрового имени возможна лишь раз в сутки.', 'Cмeнa имeни', '?set=options', 3);
			}
			else
			{
				$query = db::query("SELECT id FROM game_users WHERE username = '" . $username . "'");
				if (db::num_rows($query) == 0)
				{
					if (preg_match("/^[a-zA-Za-яA-Я0-9_\.\,\-\!\?\*\ ]+$/u", $username) && mb_strlen($username, 'UTF-8') >= 5)
					{
						db::query("UPDATE game_users SET username = '" . $username . "' WHERE id = '" . user::get()->data['id'] . "' LIMIT 1");
						db::query("UPDATE game_users_info SET username_last = '" . time() . "' WHERE id = '" . user::get()->data['id'] . "' LIMIT 1");
						db::query("INSERT INTO game_log_username VALUES (" . user::get()->data['id'] . ", " . time() . ", '" . $username . "');");

						$this->message('Уcпeшнo', 'Cмeнa имeни', '?set=login', 2);
					}
					else
						$this->message('Дaннoe имя aккayнтa cлишкoм кopoткoe или имeeт зaпpeщeнныe cимвoлы', 'Cмeнa имeни', '?set=options', 3);
				}
				else
					$this->message('Дaннoe имя aккayнтa yжe иcпoльзyeтcя в игpe', 'Cмeнa имeни', '?set=options', 3);
			}
		}

		$this->message(_getText('succeful_save'), "Hacтpoйки игpы", '?set=options', 3);
	}

	public function ld ()
	{
		if (!isset($_POST['ld']) || $_POST['ld'] == '')
			$this->message('Ввведите текст сообщения', 'Ошибка', '?set=options', 3);
		else
		{
			db::query("INSERT INTO game_private (u_id, text, time) VALUES (" . user::get()->data['id'] . ", '" . addslashes(htmlspecialchars($_POST['text'])) . "', " . time() . ")");
			
			$this->message('Запись добавлена в личное дело', 'Успешно', '?set=options', 3);
		}
	}
	
	public function show ()
	{
		$inf = db::query("SELECT * FROM game_users_info WHERE id = " . user::get()->data['id'] . "", true);

		$parse = array();

		if (user::get()->data['urlaubs_modus_time'] > 0)
		{
			$parse['um_end_date'] = datezone("d.m.Y H:i:s", user::get()->data['urlaubs_modus_time']);
			$parse['opt_delac_data'] = (user::get()->data['deltime'] > 0) ? " checked='checked'/" : '';
			$parse['opt_modev_data'] = (user::get()->data['urlaubs_modus_time'] > 0) ? " checked='checked'/" : '';
			$parse['opt_usern_data'] = user::get()->data['username'];

			$this->setTemplate('options_um');
			$this->set('parse', $parse);

			$this->setTitle('Hacтpoйки');
			$this->showTopPanel(false);
			$this->display();
		}
		else
		{
			$parse['opt_lst_ord_data'] = "<option value =\"0\"" . (($inf['planet_sort'] == 0) ? " selected" : "") . ">" . _getText('opt_lst_ord0') . "</option>";
			$parse['opt_lst_ord_data'] .= "<option value =\"1\"" . (($inf['planet_sort'] == 1) ? " selected" : "") . ">" . _getText('opt_lst_ord1') . "</option>";
			$parse['opt_lst_ord_data'] .= "<option value =\"2\"" . (($inf['planet_sort'] == 2) ? " selected" : "") . ">" . _getText('opt_lst_ord2') . "</option>";
			$parse['opt_lst_ord_data'] .= "<option value =\"3\"" . (($inf['planet_sort'] == 3) ? " selected" : "") . ">Типу</option>";

			$parse['opt_lst_cla_data'] = "<option value =\"0\"" . (($inf['planet_sort_order'] == 0) ? " selected" : "") . ">" . _getText('opt_lst_cla0') . "</option>";
			$parse['opt_lst_cla_data'] .= "<option value =\"1\"" . (($inf['planet_sort_order'] == 1) ? " selected" : "") . ">" . _getText('opt_lst_cla1') . "</option>";

			$parse['avatar'] = '';

			if ($inf['image'] != '')
			{
				$parse['avatar'] = "<img src='".RPATH."images/avatars/upload/".$inf['image']."' height='100'><br>";
			}
			elseif (user::get()->data['avatar'] != 0)
			{
				if (user::get()->data['avatar'] != 99)
					$parse['avatar'] = "<img src=".RPATH."images/faces/" . user::get()->data['sex'] . "/" . user::get()->data['avatar'] . "s.png height='100'><br>";
				else
					$parse['avatar'] = "<img src=".RPATH."images/avatars/upload/upload_" . user::get()->data['id'] . ".jpg height='100'><br>";
			}

			$parse['opt_usern_datatime'] = $inf['username_last'];
			$parse['opt_usern_data'] = user::get()->data['username'];
			$parse['opt_mail_data'] = $inf['email'];
			$parse['opt_sec_data'] = (user::get()->getUserOption('security') == 1) ? " checked='checked'" : '';
			$parse['opt_record_data'] = (user::get()->getUserOption('records') == 1) ? " checked='checked'" : '';
			$parse['opt_bbcode_data'] = (user::get()->getUserOption('bb_parser') == 1) ? " checked='checked'/" : '';
			$parse['opt_ajax_data'] = (user::get()->getUserOption('ajax_navigation') == 1) ? " checked='checked'/" : '';
			$parse['opt_gameactivity_data'] = (user::get()->getUserOption('gameactivity') == 1) ? " checked='checked'/" : '';
			$parse['opt_planetlist_data'] = (user::get()->getUserOption('planetlist') == 1) ? " checked='checked'/" : '';
			$parse['opt_planetlistselect_data'] = (user::get()->getUserOption('planetlistselect') == 1) ? " checked='checked'/" : '';
			$parse['opt_available_data'] = (user::get()->getUserOption('only_available') == 1) ? " checked='checked'/" : '';
			$parse['opt_delac_data'] = (user::get()->data['deltime'] > 0) ? " checked='checked'/" : '';
			$parse['opt_modev_data'] = (user::get()->data['urlaubs_modus_time'] > 0) ? " checked='checked'/" : '';
			$parse['sex'] = user::get()->data['sex'];
			$parse['about'] = $inf['about'];
			$parse['timezone'] = $inf['timezone'];
			$parse['spy'] = $inf['spy'];
			$parse['color'] = $inf['color'];

			$parse['auth'] = db::extractResult(db::query("SELECT * FROM game_users_auth WHERE user_id = ".user::get()->getId().""));

			$this->setTemplate('options');
			$this->set('parse', $parse);

			$this->setTitle('Hacтpoйки');
			$this->showTopPanel(false);
			$this->display();
		}
	}
}

?>