<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Friday\Core\Options;
use PHPMailer\PHPMailer\PHPMailer;
use Phalcon\Text;
use Xnova\Controller;
use Xnova\Request;

/**
 * @Route("/")
 */
class IndexController extends Controller
{
	public function initialize()
	{
		if ($this->auth->isAuthorized())
			return $this->response->redirect('overview/');

		//die();
		
		parent::initialize();

		if (!$this->dispatcher->wasForwarded())
		{
			$this->assets->clearCss();

			$this->assets->addJs('assets/js/plugins/validate.js', 'footer');
			$this->assets->addJs('assets/js/plugins/confirm.js', 'footer');

			$this->assets->addCss('assets/css/login.css');
			$this->assets->addCss('assets/css/plugins/confirm.css', 'footer');
		}

		return true;
	}

	public function indexAction ()
	{
		$this->tag->setTitle('Вход в игру');
	}

	/**
	 * @Route("/registration/")
	 */
	public function registrationAction ()
	{
		Lang::includeLang('reg', 'xnova');

		if ($this->request->isPost())
		{
			$errors = 0;
			$errorlist = "";

			$email = strip_tags(trim($this->request->getPost('email')));

			if (!is_email($email))
			{
				$errorlist .= "\"" . $email . "\" " . _getText('error_mail');
				$errors++;
			}

			if (mb_strlen($this->request->getPost('password'), 'UTF-8') < 4)
			{
				$errorlist .= _getText('error_password');
				$errors++;
			}

			if (!$this->request->hasPost('rgt') || !$this->request->hasPost('sogl') || $this->request->getPost('rgt') != 'on' || $this->request->getPost('sogl') != 'on')
			{
				$errorlist .= _getText('error_rgt');
				$errors++;
			}

			$ExistMail = $this->db->query("SELECT `id` FROM game_users_info WHERE `email` = '" . $email . "' LIMIT 1")->fetch();

			if (isset($ExistMail['id']))
			{
				$errorlist .= _getText('error_emailexist');
				$errors++;
			}

			if (!$errors)
			{
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, "secret=".$this->config->recaptcha->secret_key."&response=".$this->request->getPost('g-recaptcha-response')."&remoteip=".$this->request->getClientAddress()."");

				$captcha = json_decode(curl_exec($curl), true);

				curl_close($curl);

				if (!$captcha['success'])
				{
					$errors++;
					$errorlist .= "Неправильный регистрационный код!<br>";
				}
			}

			if ($errors != 0)
			{
				$this->view->setVar('message', $errorlist);
			}
			else
			{
				$newpass = trim($this->request->getPost('password'));
				$md5newpass = md5($newpass);

				$this->db->insertAsDict(
				   	"game_users",
					[
						'username' 		=> '',
						'sex' 			=> 0,
						'planet_id' 	=> 0,
						'ip' 			=> convertIp($this->request->getClientAddress()),
						'bonus' 		=> time(),
						'onlinetime' 	=> time()
					]
				);

				$iduser = $this->db->lastInsertId();

				$this->db->insertAsDict(
					"game_users_info",
					[
						'id' 			=> $iduser,
						'email' 		=> $email,
						'create_time' 	=> time(),
						'password' 		=> $md5newpass
					]
				);

				if ($this->session->has('ref'))
				{
					$refe = $this->db->query("SELECT id FROM game_users WHERE id = ".$this->session->get('ref'))->fetch();

					if (isset($refe['id']))
						$this->db->insertAsDict('game_refs', Array('r_id' => $iduser, 'u_id' => $this->session->get('ref')));
				}

				$total = $this->db->query("SELECT `value` FROM game_options WHERE `name` = 'users_total'")->fetch();

				Options::set('users_total', $total['value'] + 1);

				$mail = new PHPMailer();
				$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
				$mail->addAddress($this->request->getPost('email'));
				$mail->isHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->Subject = Options::get('site_title').": Регистрация";
				$mail->Body = "Вы успешно зарегистрировались в игре ".Options::get('site_title').".<br>Ваши данные для входа в игру:<br>Email: " . $this->request->getPost('email') . "<br>Пароль:" . $newpass . "";
				$mail->send();

				$this->auth->authorize($iduser, 0);

				if ($this->request->isAjax())
					Request::addData('redirect', $this->url->get('overview/'));
				else
					$this->response->redirect('overview/');

				$this->view->disable();
			}
		}
	}

	/**
	 * @Route("/remind/")
	 */
	public function remindAction ()
	{
		$message = '';

		if ($this->request->hasQuery('id') && $this->request->hasQuery('key') && is_numeric($this->request->getQuery('id')) && intval($this->request->getQuery('id')) > 0 && $this->request->getQuery('key') != "")
		{
			$id = intval($this->request->getQuery('id'));
			$key = addslashes($this->request->getQuery('key'));

			$Lost = $this->db->query("SELECT * FROM game_lostpasswords WHERE keystring = '" . $key . "' AND user_id = '" . $id . "' AND time > " . time() . "-3600 AND active = '0' LIMIT 1;")->fetch();

			if (isset($Lost['id']))
			{
				$Mail = $this->db->query("SELECT u.username, ui.email FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.id = '" . $Lost['user_id'] . "'")->fetch();

				if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9]+$/u", $key))
					$message = 'Ошибка выборки E-mail адреса!';
				elseif (empty($Mail['email']))
					$message = 'Ошибка выборки E-mail адреса!';
				else
				{
					$NewPass = Text::random(Text::RANDOM_ALNUM, 9);

					$mail = new PHPMailer();

					$mail->isMail();
					$mail->isHTML(true);
					$mail->CharSet = 'utf-8';
					$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
					$mail->addAddress($Mail['email'], Options::get('site_title'));
					$mail->Subject = 'Новый пароль в '.Options::get('site_title').'';
					$mail->Body = "Ваш новый пароль от игрового аккаунта: " . $Mail['username'] . ": " . $NewPass;
					$mail->send();

					$this->db->query("UPDATE game_users_info SET `password` ='" . md5($NewPass) . "' WHERE `id` = '" . $id . "'");
					$this->db->query("DELETE FROM game_lostpasswords WHERE user_id = '" . $id . "'");

					$message = 'Ваш новый пароль: ' . $NewPass . '. Копия пароля отправлена на почтовый ящик!';
				}
			}
			else
				$message = 'Действие данной ссылки истекло, попробуйте пройти процедуру заново!';
		}

		if ($this->request->hasPost('email'))
		{
			$inf = $this->db->query("SELECT u.*, ui.email FROM game_users u, game_users_info ui WHERE ui.email = '".addslashes(htmlspecialchars($this->request->getPost('email')))."' AND u.id = ui.id")->fetch();

			if (isset($inf['id']))
			{
				$ip = $this->request->getClientAddress();

				$key = md5($inf['id'] . date("d-m-Y H:i:s", time()) . "ыыы");

				$this->db->insertAsDict(
				   	"game_lostpasswords",
					[
						'user_id' 		=> $inf['id'],
						'keystring' 	=> $key,
						'time'			=> time(),
						'ip'			=> $ip,
						'active'		=> 0
				   	]
				);

				$mail = new PHPMailer();

				$mail->isMail();
				$mail->isHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
				$mail->addAddress($inf['email']);
				$mail->Subject = 'Восстановление забытого пароля';

				$body = "Доброго времени суток Вам!\nКто то с IP адреса " . $ip . " запросил пароль к персонажу " . $inf['username'] . " в онлайн-игре ".Options::get('site_title').".\nТак как в анкете у персонажа указан данный e-mail, то именно Вы получили это письмо.\n\n
				Для восстановления пароля перейдите по ссылке: <a href='http://".$_SERVER['HTTP_HOST']."/remind/?id=" . $inf['id'] . "&key=" . $key . "'>http://".$_SERVER['HTTP_HOST']."/remind/?id=" . $inf['id'] . "&key=" . $key . "</a>";

				$mail->Body = $body;

				if ($mail->send())
					$message = 'Ссылка на восстановления пароля отправлена на ваш E-mail';
				else
					$message = 'Произошла ошибка при отправке сообщения. Обратитесь к администратору сайта за помощью.';
			}
			else
				$message = 'Персонаж не найден в базе';
		}

		$this->view->setVar('message', $message);
	}

	/**
	 * @Route("/login/")
	 */
	public function loginAction ()
	{
		$error = '';

		if ($this->request->hasPost('email'))
		{
			if ($this->request->getPost('email') != '')
			{
				$login = $this->db->query("SELECT u.id, u.options, ui.password FROM game_users u, game_users_info ui WHERE ui.id = u.id AND ui.`email` = '" . $this->request->getPost('email') . "' LIMIT 1")->fetch();

				if (isset($login['id']))
				{
					if ($login['password'] == md5($this->request->getPost('password')))
					{
						$expiretime = $this->request->hasPost("rememberme") ? (time() + 2419200) : 0;

						$this->auth->authorize($login['id'], $expiretime);

						if ($this->request->isAjax())
						{
							Request::setStatus(true);
							Request::addData('redirect', $this->url->getBaseUri().'overview/');
						}
						else
							$this->response->redirect('overview/');

						$this->view->disable();
					}
					else
						$error = 'Неверный E-mail и/или пароль';
				}
				else
					$error = 'Игрока с таким E-mail адресом не найдено';
			}
			else
				$error = 'Введите хоть что-нибудь!';
		}

		if ($error != '')
		{
			Request::setStatus(false);
			Request::addData('messages', [[
				'type' => 'error',
				'text' => $error
			]]);
		}

		return false;
	}
}