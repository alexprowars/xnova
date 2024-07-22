<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\User;
use App\Notifications\PasswordResetSuccessNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class ResetPasswordController extends Controller
{
	public function send(Request $request)
	{
		$request->validate([
			'email' => ['required', 'email'],
		]);

		$status = Password::sendResetLink(
			$request->only('email')
		);

		if ($status != Password::RESET_LINK_SENT) {
			throw new Exception(__($status));
		}

		return [
			'message' => __($status),
		];
	}

	public function reset(Request $request)
	{
		$request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
		]);

		$email = $request->query('email');
		$token = addslashes($request->query('token'));

		$password = Str::random(10);

		$status = Password::reset(
			['email' => $email, 'token' => $token, 'password' => $password],
			function (User $user, $password) {
				$user->password = Hash::make($password);
				$user->setRememberToken(Str::random(60));
				$user->save();

				event(new PasswordReset($user));

				Auth::login($user);

				try {
					$user->notify(new PasswordResetSuccessNotification($password));
				} catch (Throwable) {
				}
			}
		);

		if ($status != Password::PASSWORD_RESET) {
			throw new Exception(__($status));
		}

		throw new Exception('Ваш новый пароль: ' . $password . '. Копия пароля отправлена на почтовый ящик!');
	}
}
