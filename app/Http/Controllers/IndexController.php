<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use App\Controller;
use App\Exceptions\Exception;
use App\Helpers;
use App\Models;
use App\Models\User;

class IndexController extends Controller
{
	public function registration(Request $request)
	{
		$errors = [];

		if ($request->isMethod('post')) {
			$email = strip_tags(trim($request->post('email')));

			if (!Helpers::is_email($email)) {
				$errors[] = '"' . $email . '" ' . __('reg.error_mail');
			}

			if (mb_strlen($request->post('password')) < 4) {
				$errors[] = __('reg.error_password');
			}

			if ($request->post('password') != $request->post('password_confirm')) {
				$errors[] = __('reg.error_confirm');
			}

			$checkExist = Models\User::query()->where('email', $email)->exists();

			if ($checkExist) {
				$errors[] = __('reg.error_emailexist');
			}

			if (empty($errors) && !empty(config('settings.recaptcha.secret_key'))) {
				$captcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
					'secret' => config('settings.recaptcha.secret_key'),
					'response' => $request->post('captcha'),
					'remoteip' => $request->ip()
				])->json();

				if (!$captcha['success']) {
					$errors[] = 'Вы не прошли проверку на бота!';
				}
			}

			if (!count($errors)) {
				$user = User::creation([
					'email' => $email,
					'password' => trim($request->post('password')),
				]);

				if (!$user) {
					throw new Exception('create user error');
				}

				Auth::login($user, true);

				return Redirect::to('overview/');
			}
		}

		return [
			'errors' => $errors
		];
	}
}
