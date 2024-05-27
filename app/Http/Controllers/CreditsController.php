<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Controller;
use App\Models;

class CreditsController extends Controller
{
	public function index(Request $request)
	{
		$parse = [];

		$userId = $request->post('userId') && (int) $request->post('userId') > 0 ?
			(int) $request->post('userId') : $this->user->id;

		$parse['id'] = $userId;

		return $parse;
	}
}
