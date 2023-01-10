<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Format;
use App\Controller;

class SearchController extends Controller
{
	protected $loadPlanet = true;

	public function index(Request $request)
	{
		$parse = [
			'result' => []
		];

		$searchtext = $request->post('searchtext', '');
		$type = $request->post('type', 'playername');

		if ($searchtext != '' && $type != '') {
			$search = null;

			switch ($type) {
				case "playername":
					$search = DB::select("SELECT u.id, u.username, u.race, p.name AS planet_name, u.ally_name, u.galaxy AS g, u.system AS s, u.planet AS p, s.total_rank FROM users u LEFT JOIN planets p ON p.id = u.planet_id LEFT JOIN statistics s ON s.id_owner = u.id AND s.stat_type = 1 WHERE u.username LIKE '%" . $searchtext . "%' LIMIT 30;");
					break;
				case "planetname":
					$search = DB::select("SELECT u.id, u.username, u.race, p.name AS planet_name, u.ally_name, p.galaxy AS g, p.system AS s, p.planet AS p, s.total_rank FROM planets p LEFT JOIN users u ON u.id = p.id_owner LEFT JOIN statistics s ON s.id_owner = u.id AND s.stat_type = 1 WHERE p.name LIKE '%" . $searchtext . "%' LIMIT 30");
					break;
				case "allytag":
					$search = DB::select("SELECT a.id, a.name, a.tag, a.members, s.total_points FROM alliances a LEFT JOIN statistics s ON s.id_owner = a.id AND s.stat_type = 2 WHERE a.tag LIKE '%" . $searchtext . "%' LIMIT 30");
					break;
				case "allyname":
					$search = DB::select("SELECT a.id, a.name, a.tag, a.members, s.total_points FROM alliances a LEFT JOIN statistics s ON s.id_owner = a.id AND s.stat_type = 2 WHERE a.name LIKE '%" . $searchtext . "%' LIMIT 30");
			}

			if (count($search)) {
				foreach ($search as $r) {
					if ($type == 'playername' || $type == 'planetname') {
						if (!$r->total_rank) {
							$r->total_rank = 0;
						}
						if (!$r->ally_name) {
							$r->ally_name = '-';
						}

						$parse['result'][] = $r;
					} elseif ($type == 'allytag' || $type == 'allyname') {
						$r->total_points = Format::number($r->total_points);

						$parse['result'][] = (array) $r;
					}
				}
			}
		}

		$parse['searchtext'] = $searchtext;
		$parse['type'] = $type;

		$this->setTitle('Поиск');

		return $parse;
	}
}
