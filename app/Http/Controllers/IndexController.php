<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
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

	public function Registration()
	{
		$errors = [];

		if (Request::instance()->isMethod('post')) {
			$email = strip_tags(trim(Request::post('email')));

			if (!Helpers::is_email($email)) {
				$errors[] = '"' . $email . '" ' . __('reg.error_mail');
			}

			if (mb_strlen(Request::post('password')) < 4) {
				$errors[] = __('reg.error_password');
			}

			if (Request::post('password') != Request::post('password_confirm')) {
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
					'secret' => Config::get('settings.recaptcha->secret_key'),
					'response' => Request::post('captcha'),
					'remoteip' => Request::ip()
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
					'password' => trim(Request::post('password')),
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
			'captcha' => Config::get('settings.recaptcha.public_key'),
			'errors' => $errors
		];
	}
}
