<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\SuccessException;
use App\Models;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class LoginController extends Controller
{
	private $socialDrivers = [
		'vkontakte',
	];

	public function byCredentials(Request $request)
	{
		if (empty($request->post('email'))) {
			throw new Exception('Введите Email');
		}

		$existUser = Models\User::query()->where('email', $request->post('email'))->exists();

		if (!$existUser) {
			throw new Exception('Игрока с таким E-mail адресом не найдено');
		}

		$credentials = $request->only(['email', 'password']);

		if (!Auth::attempt($credentials, $request->has('rememberme'))) {
			throw new Exception('Неверный E-mail и/или пароль');
		}
	}

	public function byServices($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return redirect()->away('/');
		}

		return Socialite::driver($service)->redirect();
	}

	public function servicesCallback($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return redirect()->away('/');
		}

		try {
			$user = Socialite::driver($service)->user();
		} catch (\Exception) {
			return redirect()->away('/');
		}

		$authData = Models\UserAuthentication::query()->where('provider', $service)
			->where('provider_id', $user->id)->first();

		if ($authData) {
			$authData->enter_time = time();
			$authData->save();

			Auth::loginUsingId($authData->user_id, true);
		} else {
			$user = User::creation([
				'name' => $user->getNickname() ?: $user->getName(),
				'email' => $user->email,
			]);

			if (!$user) {
				throw new Exception('create user error');
			}

			Models\UserAuthentication::create([
				'user_id' 		=> $user->id,
				'provider'		=> $service,
				'provider_id' 	=> $user->id,
				'enter_time' 	=> now(),
			]);

			Auth::login($user, true);
		}

		return redirect()->away('/overview');
	}

	public function resetPassword(Request $request)
	{
		if ($request->has(['email', 'token'])) {
			$request->validate([
				'token' => ['required'],
				'email' => ['required', 'email'],
			]);

			$email = $request->query('email');
			$token = addslashes($request->query('token'));

			$password = Str::random(10);

			$status = Password::reset(
				['email' => $email, 'token' => $token, 'password' => $password],
				function (Models\User $user, $password) {
					$user->password = Hash::make($password);
					$user->setRememberToken(Str::random(60));
					$user->save();

					event(new PasswordReset($user));

					Auth::login($user);

					try {
						$user->notify(new PasswordResetNotification($password));
					} catch (Throwable) {
					}
				}
			);

			if ($status != Password::PASSWORD_RESET) {
				throw new Exception(__($status));
			}

			throw new SuccessException('Ваш новый пароль: ' . $password . '. Копия пароля отправлена на почтовый ящик!');
		}

		if ($request->post('email')) {
			$request->validate([
				'email' => ['required', 'email'],
			]);

			$status = Password::sendResetLink(
				$request->only('email')
			);

			if ($status != Password::RESET_LINK_SENT) {
				throw new Exception(__($status));
			}

			throw new SuccessException(__($status));
		}
	}
}
