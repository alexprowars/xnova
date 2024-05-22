<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use App\Controller;
use App\Exceptions\Exception;
use App\Helpers;
use App\Models;
use App\User;

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

			$checkExist = Models\User::query()->where('email', $email)->exists();

			if ($checkExist) {
				$errors[] = __('reg.error_emailexist');
			}

			if (empty($errors)) {
				$captcha = Http::post('https://www.google.com/recaptcha/api/siteverify', [
					'secret' => config('settings.recaptcha.secret_key'),
					'response' => $request->post('captcha'),
					'remoteip' => $request->ip()
				])->json();

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
			'errors' => $errors
		];
	}
}
