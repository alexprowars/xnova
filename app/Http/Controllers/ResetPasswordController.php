<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
	public function forgot(Request $request): array
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

	public function reset(Request $request): void
	{
		$data = $request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', PasswordRule::min(6), 'confirmed'],
		]);

		$status = Password::reset(
			$data,
			function (User $user, $password) {
				$user->forceFill([
					'password' => Hash::make($password),
				]);
				$user->save();

				event(new PasswordReset($user));

				Auth::login($user);
			}
		);

		if ($status != Password::PASSWORD_RESET) {
			throw new Exception(__($status));
		}
	}
}
