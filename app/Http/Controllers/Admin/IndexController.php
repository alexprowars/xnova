<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Format;
use App\Helpers;

class IndexController extends Controller
{
	public static function getMenu()
	{
		return [[
			'code'	=> 'index',
			'title' => 'Панель управления',
			'icon'	=> 'la la-campground',
			'sort'	=> 10
		], [
			'code'	=> null,
			'title' => 'Игра',
			'icon'	=> '',
			'sort'	=> 11
		]];
	}

	public function index(Request $request)
	{
		if ($request->query('cmd') == 'sort') {
			$TypeSort = $request->query('type');
		} else {
			$TypeSort = "ip";
		}

		$parse = [];
		$parse['adm_ov_data_yourv'] = VERSION;
		$parse['adm_ov_data_table'] = [];

		$Count = 0;
		$Color = "inherit";
		$PrevIP = '';

		if (Auth::user()->can('list index:online')) {
			$Last15Mins = User::query()->where('onlinetime', '>', now()->subMinutes(15))
				->orderBy($TypeSort)
				->get();

			foreach ($Last15Mins as $TheUser) {
				if (!empty($PrevIP)) {
					if ($PrevIP == $TheUser->ip) {
						$Color = 'red';
					} else {
						$Color = 'inherit';
					}
				}

				$PrevIP = $TheUser->ip;

				$Bloc['adm_ov_altpm'] = __('admin.main.adm_ov_altpm');
				$Bloc['adm_ov_wrtpm'] = __('admin.main.adm_ov_wrtpm');
				$Bloc['adm_ov_data_id'] = $TheUser->id;
				$Bloc['adm_ov_data_name'] = $TheUser->username;
				$Bloc['adm_ov_data_clip'] = $Color;
				$Bloc['adm_ov_data_adip'] = Helpers::convertIp($TheUser->ip);
				$Bloc['adm_ov_data_ally'] = $TheUser->alliance_name;
				$Bloc['adm_ov_data_activ'] = Format::time(time() - $TheUser->onlinetime->timestamp);

				$parse['adm_ov_data_table'][] = $Bloc;
				$Count++;
			}
		}

		$parse['adm_ov_data_count'] = $Count;

		View::share('title', 'Панель управления');
		View::share('breadcrumbs', [
			'Панель управления' => false,
		]);

		return view('admin.overview', ['parse' => $parse]);
	}
}
