<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models;
use App\Models\User;
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

		$existUser = Models\User::query()->where('email', $request->post('email'))->exists();

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
}
