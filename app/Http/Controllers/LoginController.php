<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\User;
use App\Models\UserAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
	private $socialDrivers = [
		'vkontakte',
	];

	public function credentials(Request $request)
	{
		if (empty($request->post('email'))) {
			throw new Exception('Введите Email');
		}

		$existUser = User::query()->where('email', $request->post('email'))->exists();

		if (!$existUser) {
			throw new Exception('Игрока с таким E-mail адресом не найдено');
		}

		$credentials = $request->only(['email', 'password']);

		if (!Auth::attempt($credentials, $request->has('rememberme'))) {
			throw new Exception('Неверный E-mail и/или пароль');
		}
	}

	public function services($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return redirect()->away('/');
		}

		return Socialite::driver($service)->redirect();
	}

	public function callback($service)
	{
		if (!in_array($service, $this->socialDrivers)) {
			return redirect()->away('/');
		}

		try {
			$profile = Socialite::driver($service)->user();
		} catch (\Exception) {
			return redirect()->away('/');
		}

		$authData = UserAuthentication::query()->where('provider', $service)
			->where('provider_id', $profile->id)->first();

		if ($authData) {
			$authData->enter_time = time();
			$authData->save();

			Auth::loginUsingId($authData->user_id, true);
		} else {
			if (empty($profile->email)) {
				$profile->email = 'social@' . $profile->id;
			}

			$user = User::query()->where('email', $profile->email)->first();

			if (!$user) {
				$user = User::creation([
					'name' => $profile->getNickname() ?: $profile->getName(),
					'email' => $profile->email,
				]);

				if (!$user) {
					throw new Exception('create user error');
				}
			}

			UserAuthentication::create([
				'user_id' 		=> $user->id,
				'provider'		=> $service,
				'provider_id' 	=> $profile->id,
				'enter_time' 	=> now(),
			]);

			Auth::login($user, true);
		}

		return redirect()->away('/overview');
	}
}
