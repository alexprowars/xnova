<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
	public function create(RegistrationRequest $request): void
	{
		$user = User::creation($request->validated());

		Auth::login($user, true);
	}
}
