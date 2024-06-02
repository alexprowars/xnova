<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\Exception;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RegistrationController extends Controller
{
	public function index(RegistrationRequest $request)
	{
		if (!empty(config('settings.recaptcha.secret_key'))) {
			$captcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
				'secret' => config('settings.recaptcha.secret_key'),
				'response' => $request->post('captcha'),
				'remoteip' => $request->ip(),
			])->json();

			if (!$captcha['success']) {
				throw new Exception('Вы не прошли проверку на бота!');
			}
		}

		$user = User::creation($request->validated());

		if (!$user) {
			throw new Exception('create user error');
		}

		Auth::login($user, true);
	}
}
