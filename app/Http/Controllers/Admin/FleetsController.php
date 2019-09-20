<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Xnova\Fleet;
use Xnova\Game;
use Xnova\Models;

/** @noinspection PhpUnused */
class FleetsController extends Controller
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'fleets',
			'title' => 'Флоты в полёте',
			'icon'	=> 'plane',
			'sort'	=> 110
		]];
	}

	public function index ()
	{
		$items = [];

		$fleets = Models\Fleet::query()->orderBy('end_time', 'asc');

		foreach ($fleets as $fleet)
		{
			$row = [];
			$row['Id'] = $fleet->id;
			$row['Mission'] = Fleet::CreateFleetPopupedMissionLink($fleet, __('main.type_mission.'.$fleet->mission), '');
			$row['Mission'] .= "<br>" . (($fleet->mess == 1) ? "R" : "A");

			$row['Fleet'] = Fleet::CreateFleetPopupedFleetLink($fleet, __('main.tech.200'), '', $this->user);
			$row['St_Owner'] = "[" . $fleet->owner . "]<br>" . $fleet->owner_name;
			$row['St_Posit'] = "[" . $fleet->start_galaxy . ":" . $fleet->start_system . ":" . $fleet->start_planet . "]<br>" . (($fleet->start_type == 1) ? "[P]" : (($fleet->start_type == 2) ? "D" : "L")) . "";
			$row['St_Time'] = Game::datezone('H:i:s d/n/Y', $fleet->start_time);

			if (!empty($fleet->target_owner))
				$row['En_Owner'] = "[" . $fleet->target_owner . "]<br>" . $fleet->target_owner_name;
			else
				$row['En_Owner'] = "";

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