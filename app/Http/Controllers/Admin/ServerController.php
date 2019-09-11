<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;

class ServerController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'server',
			'title' => 'Информация',
			'icon'	=> 'information',
			'sort'	=> 30
		]];
	}

	public function index ()
	{
		View::share('title', 'Серверное окружение');

		return view('admin.server');
	}
}