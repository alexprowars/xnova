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
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\SuccessException;
use Xnova\Models\User;
use Xnova\Models\UserInfo;
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

		return parent::initialize();
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
		$errors = [];

		Lang::includeLang('reg', 'xnova');

		if ($this->request->isPost())
		{
			$email = strip_tags(trim($this->request->getPost('email')));

			if (!is_email($email))
				$errors[] = '"'.$email.'" '._getText('error_mail');

			if (mb_strlen($this->request->getPost('password'), 'UTF-8') < 4)
				$errors[] = _getText('error_password');

			if ($this->request->getPost('password') != $this->request->getPost('password_confirm'))
				$errors[] = _getText('error_confirm');

			$checkExist = UserInfo::count(['email = :email:', 'bind' => ['email' => $email]]) > 0;

			if ($checkExist)
				$errors[] = _getText('error_emailexist');

			if (!count($errors))
			{
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
					'secret' => $this->config->recaptcha->secret_key,
					'response' => $this->request->getPost('captcha'),
					'remoteip' => $this->request->getClientAddress()
				]));

				$captcha = json_decode(curl_exec($curl), true);

				curl_close($curl);

				if (!$captcha['success'])
					$errors[] = "Вы не прошли проверку на бота!";
			}

			if (!count($errors))
			{
				$newpass = trim($this->request->getPost('password'));
				$md5newpass = md5($newpass);

				$user = new User();
				$user->create([
					'username' 		=> '',
					'sex' 			=> 0,
					'planet_id' 	=> 0,
					'ip' 			=> convertIp($this->request->getClientAddress()),
					'bonus' 		=> time(),
					'onlinetime' 	=> time()
				]);

				if (!$user->id)
					throw new \Exception('create user error');

				$userInfo = new UserInfo();
				$userInfo->create([
					'id' 			=> $user->id,
					'email' 		=> $email,
					'create_time' 	=> time(),
					'password' 		=> $md5newpass
				]);

				if ($this->session->has('ref'))
				{
					$refer = User::findFirst((int) $this->session->get('ref'));

					if ($refer)
					{
						$this->db->insertAsDict('game_refs', [
							'r_id' => $user->id,
							'u_id' => $refer->getId()
						]);
					}
				}

				Options::set('users_total', Options::get('users_total', 0, true) + 1);

				$mail = new PHPMailer();
				$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
				$mail->addAddress($this->request->getPost('email'));
				$mail->isHTML(true);
				$mail->CharSet = 'utf-8';
				$mail->Subject = Options::get('site_title').": Регистрация";

				$template = file_get_contents(ROOT_PATH.'/app/modules/Xnova/Views/email/registration.html');
				$template = strtr($template, [
					'#SERVER#' => $this->request->getScheme().'://'.$this->request->getServerName(),
					'#EMAIL#' => $this->request->getPost('email'),
					'#PASSWORD#' => $newpass,
				]);

				$mail->Body = $template;
				$mail->send();

				$this->auth->authorize($user->id, 0);

				if ($this->request->isAjax())
					Request::addData('redirect', $this->url->get('overview/'));
				else
					$this->response->redirect('overview/');

				$this->view->disable();
			}
		}

		Request::addData('page', [
			'captcha' => $this->config->recaptcha->public_key,
			'errors' => $errors
		]);

		$this->tag->setTitle('Регистрация');
	}

	/**
	 * @Route("/remind/")
	 */
	public function remindAction ()
	{
		if ($this->request->hasQuery('id') && $this->request->hasQuery('key') && is_numeric($this->request->getQuery('id')) && intval($this->request->getQuery('id')) > 0 && $this->request->getQuery('key') != "")
		{
			$id = (int) $this->request->getQuery('id');
			$key = addslashes($this->request->getQuery('key'));

			$request = $this->db->query("SELECT * FROM game_lostpasswords WHERE keystring = '" . $key . "' AND user_id = '" . $id . "' AND time > " . time() . "-3600 AND active = '0' LIMIT 1;")->fetch();

			if (!isset($request['id']))
				throw new ErrorException('Действие данной ссылки истекло, попробуйте пройти процедуру заново!');

			$user = $this->db->query("SELECT u.username, ui.email FROM game_users u, game_users_info ui WHERE ui.id = u.id AND u.id = '" . $request['user_id'] . "'")->fetch();

			if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9]+$/u", $key))
				throw new ErrorException('Ошибка выборки E-mail адреса!');

			if (empty($user['email']))
				throw new ErrorException('Ошибка выборки E-mail адреса!');

			$password = Text::random(Text::RANDOM_ALNUM, 9);

			$mail = new PHPMailer();

			$mail->isHTML(true);
			$mail->CharSet = 'utf-8';
			$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
			$mail->addAddress($user['email'], Options::get('site_title'));
			$mail->Subject = 'Новый пароль в '.Options::get('site_title').'';

			$template = file_get_contents(ROOT_PATH.'/app/modules/Xnova/Views/email/remind_2.html');
			$template = strtr($template, [
				'#SERVER#' => $this->request->getScheme().'://'.$this->request->getServerName(),
				'#EMAIL#' => $user['username'],
				'#PASSWORD#' => $password,
			]);

			$mail->Body = $template;
			$mail->send();

			$this->db->query("UPDATE game_users_info SET `password` ='" . md5($password) . "' WHERE `id` = '" . $id . "'");
			$this->db->query("DELETE FROM game_lostpasswords WHERE user_id = '" . $id . "'");

			throw new SuccessException('Ваш новый пароль: ' . $password . '. Копия пароля отправлена на почтовый ящик!');
		}

		if ($this->request->hasPost('email'))
		{
			$inf = $this->db->query("SELECT u.*, ui.email FROM game_users u, game_users_info ui WHERE ui.email = '".addslashes(htmlspecialchars($this->request->getPost('email')))."' AND u.id = ui.id")->fetch();

			if (!isset($inf['id']))
				throw new ErrorException('Персонаж не найден в базе');

			$key = md5($inf['id'] . date("d-m-Y H:i:s", time()) . "ыыы");

			$this->db->insertAsDict("game_lostpasswords", [
				'user_id' 		=> $inf['id'],
				'keystring' 	=> $key,
				'time'			=> time(),
				'ip'			=> $this->request->getClientAddress(),
				'active'		=> 0
			]);

			$mail = new PHPMailer();

			$mail->isHTML(true);
			$mail->CharSet = 'utf-8';
			$mail->setFrom(Options::get('email_notify'), Options::get('site_title'));
			$mail->addAddress($inf['email']);
			$mail->Subject = 'Восстановление забытого пароля';

			$template = file_get_contents(ROOT_PATH.'/app/modules/Xnova/Views/email/remind_1.html');
			$template = strtr($template, [
				'#SERVER#' => $this->request->getScheme().'://'.$this->request->getServerName(),
				'#EMAIL#' => $inf['username'],
				'#URL#' => $this->url->get('/remind/?id='.$inf['id'].'&key='.$key),
			]);

			$mail->Body = $template;

			if (!$mail->send())
				throw new ErrorException('Произошла ошибка при отправке сообщения. Обратитесь к администратору сайта за помощью.');

			throw new SuccessException('Ссылка на восстановления пароля отправлена на ваш E-mail');
		}

		$this->tag->setTitle('Восстановление пароля');
	}

	/**
	 * @Route("/login/")
	 */
	public function loginAction ()
	{
		if ($this->request->hasPost('email'))
		{
			if ($this->request->getPost('email') == '')
				throw new ErrorException('Введите хоть что-нибудь!');

			$login = $this->db->query("SELECT u.id, ui.password FROM game_users u, game_users_info ui WHERE ui.id = u.id AND ui.`email` = '" . $this->request->getPost('email') . "' LIMIT 1")->fetch();

			if (!isset($login['id']))
				throw new ErrorException('Игрока с таким E-mail адресом не найдено');

			if ($login['password'] != md5($this->request->getPost('password')))
				throw new ErrorException('Неверный E-mail и/или пароль');

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

		throw new ErrorException(';)');
	}
}