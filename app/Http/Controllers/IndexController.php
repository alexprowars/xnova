<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\Exception;
use Xnova\Exceptions\SuccessException;
use Xnova\Helpers;
use Xnova\Mail\UserLostPassword;
use Xnova\Mail\UserLostPasswordSuccess;
use Xnova\Mail\UserRegistration;
use Xnova\Models\Options;
use Xnova\Models;

class IndexController extends Controller
{
	public function index ()
	{
		$this->setTitle('Вход в игру');

		return [];
	}

	public function registration ()
	{
		$errors = [];

		if (Request::instance()->isMethod('post'))
		{
			$email = strip_tags(trim(Request::post('email')));

			if (!is_email($email))
				$errors[] = '"'.$email.'" '.__('reg.error_mail');

			if (mb_strlen(Request::post('password')) < 4)
				$errors[] = __('reg.error_password');

			if (Request::post('password') != Request::post('password_confirm'))
				$errors[] = __('reg.error_confirm');

			$checkExist = Models\UsersInfo::query()->where('email', $email)->exists();

			if ($checkExist)
				$errors[] = __('reg.error_emailexist');

			if (!count($errors))
			{
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
					'secret' => Config::get('game.recaptcha->secret_key'),
					'response' => Request::post('captcha'),
					'remoteip' => Request::ip()
				]));

				$captcha = json_decode(curl_exec($curl), true);

				curl_close($curl);

				if (!$captcha['success'])
					$errors[] = "Вы не прошли проверку на бота!";
			}

			if (!count($errors))
			{
				$newpass = trim(Request::post('password'));
				$md5newpass = md5($newpass);

				/** @var Models\Users $user */
				$user = Models\Users::query()->create([
					'username' 		=> '',
					'sex' 			=> 0,
					'planet_id' 	=> 0,
					'ip' 			=> Helpers::convertIp(Request::ip()),
					'bonus' 		=> time(),
					'onlinetime' 	=> time()
				]);

				if (!$user->id)
					throw new Exception('create user error');

				Models\UsersInfo::query()->create([
					'id' 			=> $user->id,
					'email' 		=> $email,
					'create_time' 	=> time(),
					'password' 		=> $md5newpass
				]);

				if (Session::has('ref'))
				{
					/** @var Models\Users $refer */
					$refer = Models\Users::query()->find((int) Session::get('ref'), ['id']);

					if ($refer)
					{
						DB::table('refs')->insert([
							'r_id' => $user->id,
							'u_id' => $refer->getId()
						]);
					}
				}

				Options::set('game.users_total', Options::get('users_total', 0) + 1);

				Mail::to(Request::post('email'))->send(new UserRegistration([
					'#EMAIL#' => Request::post('email'),
					'#PASSWORD#' => $newpass,
				]));

				Auth::loginUsingId($user->id, true);

				return Redirect::to('overview/');
			}
		}

		$this->setTitle('Регистрация');

		return [
			'captcha' => Config::get('game.recaptcha.public_key'),
			'errors' => $errors
		];
	}

	public function remind ()
	{
		if (Request::has('id') && Request::has('key') && (int) Request::query('id') > 0 && Request::query('key') != '')
		{
			$id = (int) Request::query('id');
			$key = addslashes(Request::query('key'));

			$request = DB::table('lostpasswords')
				->where('keystring', $key)
				->where('user_id', $id)
				->where('active', 0)
				->where('time', '>', time() - 3600)
				->first();

			if (!$request)
				throw new ErrorException('Действие данной ссылки истекло, попробуйте пройти процедуру заново!');

			$user = DB::selectOne("SELECT u.username, ui.email FROM users u, users_info ui WHERE ui.id = u.id AND u.id = '" . $request['user_id'] . "'");

			if (!preg_match("/^[А-Яа-яЁёa-zA-Z0-9]+$/u", $key))
				throw new ErrorException('Ошибка выборки E-mail адреса!');

			if (empty($user->email))
				throw new ErrorException('Ошибка выборки E-mail адреса!');

			$password = Str::random(9);

			DB::table('users_info')->where('id', $id)->update(['password' => md5($password)]);
			DB::table('lostpasswords')->where('user_id', $id)->delete();

			Mail::to($user->email)->send(new UserLostPasswordSuccess([
				'#EMAIL#' => $user->username,
				'#PASSWORD#' => $password,
			]));

			throw new SuccessException('Ваш новый пароль: ' . $password . '. Копия пароля отправлена на почтовый ящик!');
		}

		if (Request::post('email'))
		{
			$inf = DB::selectOne("SELECT u.*, ui.email FROM users u, users_info ui WHERE ui.email = '".addslashes(htmlspecialchars(Request::post('email')))."' AND u.id = ui.id");

			if (!$inf)
				throw new ErrorException('Персонаж не найден в базе');

			$key = md5($inf->id . date("d-m-Y H:i:s", time()) . "ыыы");

			DB::table('lostpasswords')->insert([
				'user_id' 		=> $inf->id,
				'keystring' 	=> $key,
				'time'			=> time(),
				'ip'			=> Request::ip(),
				'active'		=> 0
			]);

			$mail = Mail::to($inf->email)->send(new UserLostPassword([
				'#EMAIL#' => $inf->username,
				'#URL#' => URL::to('remind/?id='.$inf->id.'&key='.$key),
			]));

			if (!$mail)
				throw new ErrorException('Произошла ошибка при отправке сообщения. Обратитесь к администратору сайта за помощью.');

			throw new SuccessException('Ссылка на восстановления пароля отправлена на ваш E-mail');
		}

		$this->setTitle('Восстановление пароля');
	}

	public function login ()
	{
		if (!Request::has('email'))
			throw new ErrorException(';)');

		if (Request::post('email') == '')
			throw new ErrorException('Введите хоть что-нибудь!');

		$isExist = Models\UsersInfo::query()->where('email', Request::post('email'))->exists();

		if (!$isExist)
			throw new ErrorException('Игрока с таким E-mail адресом не найдено');

		$credentials = Request::only(['email', 'password']);

		if (!Auth::attempt($credentials, Request::has('rememberme')))
			throw new ErrorException('Неверный E-mail и/или пароль');

		return Redirect::intended('overview/');
	}
}