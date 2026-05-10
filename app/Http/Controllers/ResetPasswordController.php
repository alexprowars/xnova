<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;
use Throwable;

class ResetPasswordController extends Controller
{
	public function remind(Request $request)
	{
		$component = $request->hasHeader(Modal::HEADER_MODAL)
			? 'Index/RemindModal' : 'Index/Remind';

		return Inertia::modal($component);
	}

	public function resetPage()
	{
		return Inertia::render('ResetPassword');
	}

	public function forgot(Request $request)
	{
		try {
			$request->validate([
				'email' => ['required', 'email'],
			]);

			$status = Password::sendResetLink(
				$request->only('email')
			);

			if ($status != Password::RESET_LINK_SENT) {
				throw new Exception(__($status), 422);
			}

			return response()->json(['type' => 'success', 'message' => __($status)]);
		} catch (Throwable $e) {
			return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
		}
	}

	public function reset(Request $request)
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
			throw new PageException(__($status));
		}

		return to_route('overview');
	}
}
