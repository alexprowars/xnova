<?php

namespace Xnova\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\Exception;
use Xnova\Exceptions\SuccessException;
use Xnova\Mail\UserLostPasswordSuccess;
use Xnova\User;
use Xnova\Models;

/** @noinspection PhpUnused */
class LoginController extends Controller
{
	private $socialDrivers = [
		'facebook',
		'vkontakte',
		'google',
	];

	public function LoginByCredentials ()
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

	public function LoginBySocialServices ($service)
	{
		if (!in_array($service, $this->socialDrivers))
			return Redirect::to('/');

		return Socialite::driver($service)->redirect();
	}

	public function SocialServicesCallback ($service)
	{
		if (!in_array($service, $this->socialDrivers))
			return Redirect::to('/');

		try {
			/** @var SocialiteUser $user */
			$user = Socialite::driver($service)->user();
		}
		catch (\Exception $e) {
			return Redirect::to('/');
		}

		/** @var Models\UsersAuth $authData */
		$authData = Models\UsersAuth::query()->where('service', $service)
			->where('service_id', $user->id)->first();

		if ($authData)
		{
			$authData->enter_time = time();
			$authData->update();

			Auth::loginUsingId($authData->user_id, true);
		}
		else
		{
			$userId = User::creation([
				'name' => $user->getNickname() != '' ? $user->getNickname() : $user->getName(),
				'email' => $user->email,
			]);

			if (!$userId)
				throw new Exception('create user error');

			Models\UsersAuth::query()->insert([
				'user_id' 		=> $userId,
				'service'		=> $service,
				'service_id' 	=> $user->id,
				'create_time' 	=> time(),
				'enter_time' 	=> time()
			]);

			Auth::loginUsingId($userId, true);
		}

		return Redirect::intended('overview/');
	}

	public function ResetPassword ()
	{
		$this->setTitle('Восстановление пароля');

		if (Request::has(['email', 'token']))
		{
			$email = (int) Request::query('email');
			$token = addslashes(Request::query('token'));

			/** @var PasswordBroker $broker */
			/** @noinspection PhpUndefinedMethodInspection */
			$broker = Password::broker();

			$password = Str::random(10);

			$response = $broker->reset(
				['email' => $email, 'token' => $token, 'password' => $password],
				function (Models\Users $user, $password)
				{
					Models\UsersInfo::query()->where('id', $user->id)->update([
						'password' => Hash::make($password)
					]);

					event(new PasswordReset($user));

					Auth::login($user);

					try
					{
						$email = $user->getEmailForPasswordReset();

						Mail::to($email)->send(new UserLostPasswordSuccess([
							'#EMAIL#' => $email,
							'#NAME#' => $user->username,
							'#PASSWORD#' => $password,
						]));
					}
					catch (\Exception $e) {}
			});

			if ($response == PasswordBroker::INVALID_TOKEN)
				throw new ErrorException('Действие данной ссылки истекло, попробуйте пройти процедуру заново!');

			if ($response == PasswordBroker::INVALID_USER)
				throw new ErrorException('Аккаунт не найден');

			throw new SuccessException('Ваш новый пароль: '.$password.'. Копия пароля отправлена на почтовый ящик!');
		}

		if (Request::post('email'))
		{
			/** @var PasswordBroker $broker */
			/** @noinspection PhpUndefinedMethodInspection */
			$broker = Password::broker();

			$response = $broker->sendResetLink(['email' => Request::post('email')]);

			if ($response == PasswordBroker::INVALID_USER)
				throw new ErrorException('Аккаунт не найден');

			throw new SuccessException('Ссылка на восстановления пароля отправлена на ваш E-mail');
		}

		return [];
	}
}