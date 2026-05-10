<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;

class RegistrationController extends Controller
{
	public function index(Request $request)
	{
		$component = $request->hasHeader(Modal::HEADER_MODAL)
			? 'Registration/Modal' : 'Registration/Form';

		return Inertia::modal($component);
	}

	public function create(RegistrationRequest $request)
	{
		$user = UserService::creation($request->validated(), true);

		Auth::login($user, true);

		return to_route('start');
	}
}
