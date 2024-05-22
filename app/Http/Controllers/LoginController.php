<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use App\Controller;
use App\Exceptions\ErrorException;
use App\Exceptions\Exception;
use App\Exceptions\SuccessException;
use App\Mail\UserLostPasswordSuccess;
use App\User;
use App\Models;

class LoginController extends Controller
{
	private $socialDrivers = [
		'vkontakte',
	];

	public function LoginByCredentials(Request $request)
	{
		if (!$request->has('email')) {
			throw new ErrorException(';)');
		}

		if ($request->post('email') == '') {
			throw new ErrorException('Введите хоть что-нибудь!');
		}

		$isExist = Models\UserDetail::query()->where('email', $request->post('email'))->exists();

		if (!$isExist) {
			throw new ErrorException('Игрока с таким E-mail адресом не найдено');
		}

		$credentials = $request->only(['email', 'password']);

		if (!Auth::attempt($credentials, $request->has('rememberme'))) {
			throw new ErrorException('Неверный E-mail и/или пароль');
		}

		return Redirect::intended('overview/');
	}

	public function LoginBySocialServices($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return Redirect::to('/');
		}

		return Socialite::driver($service)->redirect();
	}

	public function SocialServicesCallback($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return Redirect::to('/');
		}

		try {
			/** @var SocialiteUser $user */
			$user = Socialite::driver($service)->user();
		} catch (\Exception $e) {
			return Redirect::to('/');
		}

		$authData = Models\Authentication::query()->where('provider', $service)
			->where('provider_id', $user->id)->first();

		if ($authData) {
			$authData->enter_time = time();
			$authData->update();

			Auth::loginUsingId($authData->user_id, true);
		} else {
			$userId = User::creation([
				'name' => $user->getNickname() != '' ? $user->getNickname() : $user->getName(),
				'email' => $user->email,
			]);

			if (!$userId) {
				throw new Exception('create user error');
			}

			Models\Authentication::query()->insert([
				'user_id' 		=> $userId,
				'provider'		=> $service,
				'provider_id' 	=> $user->id,
				'create_time' 	=> time(),
				'enter_time' 	=> time()
			]);

			Auth::loginUsingId($userId, true);
		}

		return Redirect::intended('overview/');
	}

	public function ResetPassword(Request $request)
	{
		if ($request->has(['email', 'token'])) {
			$email = (int) $request->query('email');
			$token = addslashes($request->query('token'));

			$broker = Password::broker();

			$password = Str::random(10);

			$response = $broker->reset(
				['email' => $email, 'token' => $token, 'password' => $password],
				function (Models\User $user, $password) {
					$user->password = Hash::make($password);
					$user->save();

					event(new PasswordReset($user));

					Auth::login($user);

					try {
						$email = $user->getEmailForPasswordReset();

						Mail::to($email)->send(new UserLostPasswordSuccess([
							'#EMAIL#' => $email,
							'#NAME#' => $user->username,
							'#PASSWORD#' => $password,
						]));
					} catch (\Exception) {
					}
				}
			);

			if ($response == PasswordBroker::INVALID_TOKEN) {
				throw new ErrorException('Действие данной ссылки истекло, попробуйте пройти процедуру заново!');
			}

			if ($response == PasswordBroker::INVALID_USER) {
				throw new ErrorException('Аккаунт не найден');
			}

			throw new SuccessException('Ваш новый пароль: ' . $password . '. Копия пароля отправлена на почтовый ящик!');
		}

		if ($request->post('email')) {
			$broker = Password::broker();

			$response = $broker->sendResetLink(['email' => $request->post('email')]);

			if ($response == PasswordBroker::INVALID_USER) {
				throw new ErrorException('Аккаунт не найден');
			}

			throw new SuccessException('Ссылка на восстановления пароля отправлена на ваш E-mail');
		}

		return [];
	}
}
