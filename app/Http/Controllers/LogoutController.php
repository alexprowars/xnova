<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Controller;
use App\Exceptions\PageException;

class LogoutController extends Controller
{
	public function index()
	{
		Auth::logout();

		throw new PageException('Вы вышли из игры', '/');
	}
}
