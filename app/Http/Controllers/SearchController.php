<?php

namespace App\Http\Controllers;

use App\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
	public function index(Request $request)
	{
		$query = $request->post('query', '');
		$type = $request->post('type', 'playername');

		if (empty($query) || empty($type)) {
			return [];
		}

		$items = [];

		$search = null;

		switch ($type) {
			case 'playername':
				$search = DB::select("SELECT u.id, u.username, u.race, p.name AS planet_name, u.alliance_name, u.galaxy AS g, u.system AS s, u.planet AS p, s.total_rank FROM users u LEFT JOIN planets p ON p.id = u.planet_id LEFT JOIN statistics s ON s.user_id = u.id AND s.stat_type = 1 WHERE u.username LIKE '%" . $query . "%' LIMIT 30;");
				break;
			case 'planetname':
				$search = DB::select("SELECT u.id, u.username, u.race, p.name AS planet_name, u.alliance_name, p.galaxy AS g, p.system AS s, p.planet AS p, s.total_rank FROM planets p LEFT JOIN users u ON u.id = p.user_id LEFT JOIN statistics s ON s.user_id = u.id AND s.stat_type = 1 WHERE p.name LIKE '%" . $query . "%' LIMIT 30");
				break;
			case 'allytag':
				$search = DB::select("SELECT a.id, a.name, a.tag, a.members_count, s.total_points FROM alliances a LEFT JOIN statistics s ON s.alliance_id = a.id AND s.stat_type = 2 WHERE a.tag LIKE '%" . $query . "%' LIMIT 30");
				break;
			case 'allyname':
				$search = DB::select("SELECT a.id, a.name, a.tag, a.members_count, s.total_points FROM alliances a LEFT JOIN statistics s ON s.alliance_id = a.id AND s.stat_type = 2 WHERE a.name LIKE '%" . $query . "%' LIMIT 30");
		}

		foreach ($search as $r) {
			if ($type == 'playername' || $type == 'planetname') {
				if (!$r->total_rank) {
					$r->total_rank = 0;
				}
				if (!$r->alliance_name) {
					$r->alliance_name = '-';
				}

				$items[] = $r;
			} elseif ($type == 'allytag' || $type == 'allyname') {
				$r->total_points = Format::number($r->total_points);

				$items[] = (array) $r;
			}
		}

		return $items;
	}
}
