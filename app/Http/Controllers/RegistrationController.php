<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
	public function create(RegistrationRequest $request)
	{
		$user = UserService::creation($request->validated(), true);

		Auth::login($user, true);

		return to_route('start');
	}
}
