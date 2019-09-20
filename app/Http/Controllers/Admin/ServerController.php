<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

/** @noinspection PhpUnused */
class ServerController extends Controller
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'server',
			'title' => 'Информация',
			'icon'	=> 'server',
			'sort'	=> 30
		]];
	}

	public function index ()
	{
		View::share('title', 'Переменные сервера');
		View::share('breadcrumbs', [
			'Панель управления' => backpack_url('/'),
			'Переменные сервера' => false,
		]);

		return view('admin.server');
	}
}