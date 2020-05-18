<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Xnova\Controller;
use Xnova\Exceptions\Exception;
use Xnova\Helpers;
use Xnova\Models;
use Xnova\User;

class IndexController extends Controller
{
	public function index()
	{
		$this->setTitle('Вход в игру');

		return [];
	}

	public function Registration(Request $request)
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

			$checkExist = Models\Account::query()->where('email', $email)->exists();

			if ($checkExist) {
				$errors[] = __('reg.error_emailexist');
			}

			if (!count($errors)) {
				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
					'secret' => config('settings.recaptcha->secret_key'),
					'response' => $request->post('captcha'),
					'remoteip' => $request->ip()
				]));

				$captcha = json_decode(curl_exec($curl), true);

				curl_close($curl);

				if (!$captcha['success']) {
					$errors[] = "Вы не прошли проверку на бота!";
				}
			}

			if (!count($errors)) {
				$userId = User::creation([
					'email' => $email,
					'password' => trim($request->post('password')),
				]);

				if (!$userId) {
					throw new Exception('create user error');
				}

				Auth::loginUsingId($userId, true);

				return Redirect::to('overview/');
			}
		}

		$this->setTitle('Регистрация');

		return [
			'captcha' => config('settings.recaptcha.public_key'),
			'errors' => $errors
		];
	}
}
