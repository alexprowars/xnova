<?php

namespace App\Http\Controllers\Admin;

use App\Engine\Fleet;
use App\Engine\Game;
use App\Models;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class FleetsController extends Controller
{
	public static function getMenu()
	{
		return [[
			'code'	=> 'fleets',
			'title' => 'Флоты в полёте',
			'icon'	=> 'plane',
			'sort'	=> 110
		]];
	}

	public function index()
	{
		$items = [];

		$fleets = Models\Fleet::query()->orderBy('end_time');

		foreach ($fleets as $fleet) {
			$row = [];
			$row['Id'] = $fleet->id;
			$row['Mission'] = Fleet::createFleetPopupedMissionLink($fleet, __('main.type_mission.' . $fleet->mission), '');
			$row['Mission'] .= "<br>" . (($fleet->mess == 1) ? "R" : "A");

			$row['Fleet'] = Fleet::createFleetPopupedFleetLink($fleet, __('main.tech.200'), '', $this->user);
			$row['St_Owner'] = "[" . $fleet->user_id . "]<br>" . $fleet->user_name;
			$row['St_Posit'] = "[" . $fleet->start_galaxy . ":" . $fleet->start_system . ":" . $fleet->start_planet . "]<br>" . (($fleet->start_type == 1) ? "[P]" : (($fleet->start_type == 2) ? "D" : "L")) . "";
			$row['St_Time'] = Game::datezone('H:i:s d/n/Y', $fleet->start_time);

			if (!empty($fleet->target_user_id)) {
				$row['En_Owner'] = "[" . $fleet->target_user_id . "]<br>" . $fleet->target_user_name;
			} else {
				$row['En_Owner'] = "";
			}

			$row['En_Posit'] = "[" . $fleet->end_galaxy . ":" . $fleet->end_system . ":" . $fleet->end_planet . "]<br>" . (($fleet->end_type == 1) ? "[P]" : (($fleet->end_type == 2) ? "D" : "L")) . "";

			$row['En_Time'] = Game::datezone('H:i:s d/n/Y', $fleet->end_time);

			$items[] = $row;
		}

		View::share('title', __('admin.page_title.fleets_index'));
		View::share('breadcrumbs', [
			'Панель управления' => backpack_url('/'),
			__('admin.page_title.fleets_index') => false,
		]);

		return view('admin.fleets', ['items' => $items]);
	}
}
