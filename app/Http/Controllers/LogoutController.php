<?php

namespace App\Http\Controllers;

use App\Exceptions\PageException;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
	public function index()
	{
		Auth::logout();

		throw new PageException('Вы вышли из игры', '/');
	}
}
