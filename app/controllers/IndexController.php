<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;
use App\Models\User;
use App\Mail\PHPMailer;
use Phalcon\Text;

class IndexController extends ApplicationController
{
	public function initialize()
	{
		if ($this->auth->isAuthorized())
			$this->response->redirect('overview/');

		parent::initialize();

		if (!$this->dispatcher->wasForwarded())
		{
			$js = $this->assets->collection('js');
			$js->addJs('/assets/js/jquery.validate.js');

			$css = $this->assets->collection('css');
			$css->addCss('/assets/css/login.css');
		}
	}

	public function indexAction ()
	{

	}

	public function registrationAction ()
	{
		Lang::includeLang('reg');

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

				$this->db->query("UPDATE game_config SET `value` = `value` + 1 WHERE `key` = 'users_total'");
				$this->config->app->users_total++;

				$mail = new PHPMailer();
				$mail->SetFrom($this->config->app->email, $this->config->app->name);
				$mail->AddAddress($this->request->getPost('email'));
				$mail->IsHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->Subject = $this->config->app->name.": Регистрация";
				$mail->Body = "Вы успешно зарегистрировались в игре ".$this->config->app->name.".<br>Ваши данные для входа в игру:<br>Email: " . $this->request->getPost('email') . "<br>Пароль:" . $newpass . "";
				$mail->Send();

				$this->auth->auth($iduser, $md5newpass, 0, 0);

				if ($this->request->isAjax())
				{
					$this->game->setRequestStatus(1);
					$this->game->setRequestData(['redirect' => $this->url->getBaseUri().'overview/']);
				}
				else
					$this->response->redirect('overview/');

				$this->view->disable();
			}
		}
	}

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

					$mail->IsMail();
					$mail->IsHTML(true);
					$mail->CharSet = 'utf-8';
					$mail->SetFrom($this->config->app->email, $this->config->app->name);
					$mail->AddAddress($Mail['email'], $this->config->app->name);
					$mail->Subject = 'Новый пароль в '.$this->config->app->name.'';
					$mail->Body = "Ваш новый пароль от игрового аккаунта: " . $Mail['username'] . ": " . $NewPass;
					$mail->Send();

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

				$mail->IsMail();
				$mail->IsHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->SetFrom($this->config->app->email, $this->config->app->name);
				$mail->AddAddress($inf['email']);
				$mail->Subject = 'Восстановление забытого пароля';

				$body = "Доброго времени суток Вам!\nКто то с IP адреса " . $ip . " запросил пароль к персонажу " . $inf['username'] . " в онлайн-игре ".$this->config->app->name.".\nТак как в анкете у персонажа указан данный e-mail, то именно Вы получили это письмо.\n\n
				Для восстановления пароля перейдите по ссылке: <a href='http://".$_SERVER['HTTP_HOST']."/index/remind/?id=" . $inf['id'] . "&key=" . $key . "'>http://".$_SERVER['HTTP_HOST']."/index/reminder/?id=" . $inf['id'] . "&key=" . $key . "</a>";

				$mail->Body = $body;

				if ($mail->Send())
					$message = 'Ссылка на восстановления пароля отправлена на ваш E-mail';
				else
					$message = 'Произошла ошибка при отправке сообщения. Обратитесь к администратору сайта за помощью.';
			}
			else
				$message = 'Персонаж не найден в базе';
		}

		$this->view->setVar('message', $message);
	}

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
						$user = new User;
						$options = $user->unpackOptions($login['options']);
						$expiretime = $this->request->hasPost("rememberme") ? (time() + 2419200) : 0;

						$this->auth->auth($login['id'], $login['password'], $options['security'], $expiretime);

						if ($this->request->isAjax())
						{
							$this->game->setRequestStatus(1);
							$this->game->setRequestData(['redirect' => $this->url->getBaseUri().'overview/']);
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
			$this->game->setRequestMessage($error);
			$this->game->setRequestStatus(0);
		}

		return false;
	}
}