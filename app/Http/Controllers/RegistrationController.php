<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
	public function index(RegistrationRequest $request)
	{
		$user = User::creation($request->validated());

		if (!$user) {
			throw new Exception('create user error');
		}

		Auth::login($user, true);
	}
}
