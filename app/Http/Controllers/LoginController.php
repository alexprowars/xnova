<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\User;
use App\Models\UserAuthentication;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class LoginController extends Controller
{
	private array $socialDrivers = [
		'vkid',
	];

	public function credentials(Request $request)
	{
		try {
			if (empty($request->post('email'))) {
				throw new Exception('Введите Email');
			}

			$exist = User::query()
				->where('email', $request->post('email'))
				->exists();

			if (!$exist) {
				throw new Exception('Игрок с таким E-mail адресом и паролем не найден');
			}

			$credentials = $request->only(['email', 'password']);

			if (!Auth::attempt($credentials, $request->has('rememberme'))) {
				throw new Exception('Неверный E-mail и/или пароль');
			}
		} catch (Throwable $e) {
			return back()->withErrors(['error' => $e->getMessage()]);
		}

		return to_route('overview');
	}

	public function services(string $service): RedirectResponse
	{
		if (!in_array($service, $this->socialDrivers)) {
			return redirect()->away('/');
		}

		return Socialite::driver($service)->redirect();
	}

	public function callback(string $service): RedirectResponse
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
			->where('provider_id', $profile->getId())->first();

		if ($authData) {
			$authData->login_date = now();
			$authData->save();

			Auth::loginUsingId($authData->user_id, true);
		} else {
			$email = $profile->getEmail();

			if (empty($email)) {
				$email = 'social@' . $profile->getId();
			}

			$user = User::query()->where('email', $email)->first();

			if (!$user) {
				$user = UserService::creation([
					'name' => $profile->getNickname() ?: $profile->getName(),
					'email' => $email,
				], true);
			}

			UserAuthentication::create([
				'user_id' 		=> $user->id,
				'provider'		=> $service,
				'provider_id' 	=> $profile->getId(),
				'login_date' 	=> now(),
			]);

			Auth::login($user, true);
		}

		return redirect()->away('/overview');
	}
}
