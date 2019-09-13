<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Entity\Building;
use Xnova\Entity\Fleet;
use Xnova\Format;
use Xnova\Helpers;

class IndexController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'index',
			'title' => 'Dashboard',
			'icon'	=> 'architecture-and-city',
			'sort'	=> 10
		], [
			'code'	=> null,
			'title' => 'Игра',
			'icon'	=> '',
			'sort'	=> 11
		]];
	}

	public function index ()
	{
		if (Request::query('cmd') == 'sort')
			$TypeSort = Request::query('type');
		else
			$TypeSort = "ip";

		$parse = [];
		$parse['adm_ov_data_yourv'] = VERSION;
		$parse['adm_ov_data_table'] = [];

		$Count = 0;
		$Color = "inherit";
		$PrevIP = '';

		if (Auth::user()->can('list index:online'))
		{
			$Last15Mins = DB::select("SELECT `id`, `username`, `ip`, `ally_name`, `onlinetime` FROM users WHERE `onlinetime` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $TypeSort . "` ASC;");

			foreach ($Last15Mins as $TheUser)
			{
				if ($PrevIP != "")
				{
					if ($PrevIP == $TheUser->ip)
						$Color = "red";
					else
						$Color = "inherit";
				}

				$PrevIP = $TheUser->ip;

				$Bloc['adm_ov_altpm'] = __('admin.main.adm_ov_altpm');
				$Bloc['adm_ov_wrtpm'] = __('admin.main.adm_ov_wrtpm');
				$Bloc['adm_ov_data_id'] = $TheUser->id;
				$Bloc['adm_ov_data_name'] = $TheUser->username;
				$Bloc['adm_ov_data_clip'] = $Color;
				$Bloc['adm_ov_data_adip'] = Helpers::convertIp($TheUser->ip);
				$Bloc['adm_ov_data_ally'] = $TheUser->ally_name;
				$Bloc['adm_ov_data_activ'] = Format::time(time() - $TheUser->onlinetime);

				$parse['adm_ov_data_table'][] = $Bloc;
				$Count++;
			}
		}

		$parse['adm_ov_data_count'] = $Count;

		View::share('title', 'Активность на сервере');

		return view('admin.overview', ['parse' => $parse]);
	}
}