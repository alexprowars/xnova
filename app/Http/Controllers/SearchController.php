<?php

namespace App\Http\Controllers;

use App\Format;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SearchController extends Controller
{
	public function index(Request $request)
	{
		$querySearch = $request->post('query', '');
		$type = $request->post('type', 'playername');

		$items = [];

		$search = null;

		switch ($type) {
			case 'playername':
			case 'planetname':
				$search = DB::query()
					->select(['u.id', 'u.username', 'u.race', 'p.name AS planet_name', 'u.alliance_name', 'u.galaxy AS g', 'u.system AS s', 'u.planet AS p', 's.total_rank'])
					->from('users', 'u')
					->leftJoin('planets as p', 'p.id', '=', 'u.planet_id')
					->leftJoin('statistics as s', function (JoinClause $join) {
						$join->on('s.user_id', '=', 'u.id');
						$join->on('s.stat_type', '=', DB::raw(1));
					})
					->when(
						$type == 'playername',
						function (Builder $query) use ($querySearch) {
							$query->whereLike('u.username', '%' . $querySearch . '%');
						}
					)
					->when(
						$type == 'planetname',
						function (Builder $query) use ($querySearch) {
							$query->whereLike('p.name', '%' . $querySearch . '%');
						}
					)
					->limit(30)
					->get();
				break;

			case 'allytag':
			case 'allyname':
				$search = DB::query()
					->select(['a.id', 'a.name', 'a.tag', 'a.total_members', 's.total_points'])
					->from('alliances', 'a')
					->leftJoin('statistics as s', function ($join) {
						$join->on('s.alliance_id', '=', 'a.id');
						$join->on('s.stat_type', '=', DB::raw(2));
					})
					->when(
						$type == 'allytag',
						function (Builder $query) use ($querySearch) {
							$query->whereLike('a.tag', '%' . $querySearch . '%');
						}
					)
					->when(
						$type == 'allyname',
						function (Builder $query) use ($querySearch) {
							$query->whereLike('a.name', '%' . $querySearch . '%');
						}
					)
					->limit(30)
					->get();
		}

		if ($search) {
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
		}

		return Inertia::render('Search', [
			'items' => $items,
		]);
	}
}
