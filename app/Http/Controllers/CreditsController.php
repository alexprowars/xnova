<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CreditsController extends Controller
{
	public function pay(Request $request)
	{
		$result = [];

		$userId = $request->post('userId') && (int) $request->post('userId') > 0 ?
			(int) $request->post('userId') : $this->user->id;

		$result['id'] = $userId;

		return $result;
	}
}
