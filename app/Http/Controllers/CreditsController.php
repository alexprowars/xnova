<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditsController extends Controller
{
	public function index()
	{
		return Inertia::render('Credits');
	}

	public function pay(Request $request): array
	{
		$result = [];

		$userId = $request->post('userId') && (int) $request->post('userId') > 0 ?
			(int) $request->post('userId') : $this->user->id;

		$result['id'] = $userId;

		return $result;
	}
}
