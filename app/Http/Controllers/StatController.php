<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Controller;
use App\Models\Statistic;

class StatController extends Controller
{
	private $field;
	private $page;

	public function __construct()
	{
		parent::__construct();

		$this->page = (int) Request::input('page', 0);
		$this->page = max($this->page, 1);

		$type = (int) Request::input('type', 1);
		$view = Request::input('view', 'players');

		if ($view != 'players' && $type > 5) {
			$type = 1;
		}

		$this->field = match ($type) {
			2 => 'fleet',
			3 => 'tech',
			4 => 'defs',
			5 => 'build',
			6 => 'minier',
			7 => 'raid',
			default => 'total',
		};
	}

	public function index()
	{
		$type = (int) Request::input('type', 1);

		$parse = [
			'update' => Date::createFromTimestamp(config('settings.statUpdate'), config('app.timezone'))->utc()->toAtomString(),
			'list' => 'players',
			'type' => $type,
			'page' => 1
		];

		if (!$this->page && Auth::check()) {
			$records = Cache::remember('app::records_' . $this->user->id, 1800, function () {
				$records = Statistic::query()
					->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
					->where('stat_type', 1)
					->where('stat_code', 1)
					->where('user_id', $this->user->id)
					->first();

				return $records?->toArray();
			});

			if ($records) {
				$this->page = $records[$this->field . '_rank'];
			}
		}

		$users = (int) config('settings.activeUsers', 0);

		$parse['elements'] = $users;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		if ($type == 6 || $type == 7) {
			$query = DB::select("SELECT u.username, u.race, u.id as user_id, a.name as alliance_name, u.alliance_id as alliance_id, u.lvl_" . $this->field . " as " . $this->field . "_points, 0 as " . $this->field . "_old_rank FROM users u LEFT JOIN alliances a ON a.id = u.alliance_id WHERE 1 = 1 ORDER BY u.lvl_" . $this->field . " DESC, u.xp" . $this->field . " DESC LIMIT " . $position . ", 100");
		} else {
			$query = DB::select("SELECT s.*, u.username, u.race FROM statistics s LEFT JOIN users u ON u.id = s.user_id WHERE s.stat_type = '1' AND s.stat_code = '1' AND s.stat_hide = 0 ORDER BY s." . $this->field . "_rank ASC LIMIT " . $position . ", 100");
		}

		$position++;

		$parse['items'] = [];

		foreach ($query as $item) {
			$row = [];
			$row['id'] = (int) $item->user_id;
			$row['place'] = $position;

			$oldPosition = (int) $item->{$this->field . '_old_rank'};

			if ($oldPosition == 0) {
				$oldPosition = $position;
			}

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item->username;

			$row['alliance'] = false;

			if ($item->alliance_id) {
				$row['alliance'] = [
					'id' => (int) $item->alliance_id,
					'name' => $item->alliance_name,
					'marked' => Auth::check() && $item->alliance_name == $this->user->alliance?->name
				];
			}

			$row['race'] = (int) $item->race;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return response()->state($parse);
	}

	public function alliances()
	{
		$type = (int) Request::input('type', 1);

		$parse = [
			'update' => Date::createFromTimestamp(config('settings.statUpdate'), config('app.timezone'))->utc()->toAtomString(),
			'list' => 'alliances',
			'type' => $type,
			'page' => 1
		];

		$alliances = (int) config('settings.activeAlliance', 0);

		$parse['elements'] = $alliances;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		$query = DB::select("SELECT s.*, a.`tag`, a.`name`, a.`members_count` FROM statistics s, alliances a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.alliance_id ORDER BY s.`" . $this->field . "_rank` ASC LIMIT " . $position . ",100;");

		$position++;

		$parse['items'] = [];

		foreach ($query as $item) {
			$row = [];
			$row['id'] = (int) $item->alliance_id;
			$row['position'] = $position;

			$oldPosition = (int) $item->{$this->field . '_old_rank'};

			if ($oldPosition == 0) {
				$oldPosition = $position;
			}

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item->name;
			$row['name_marked'] = Auth::check() && $item->name == $this->user->alliance_name;
			$row['members'] = (int) $item->members_count;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return response()->state($parse);
	}

	public function races()
	{
		$parse = [
			'update' => Date::createFromTimestamp(config('settings.statUpdate'), config('app.timezone'))->utc()->toAtomString(),
			'list' => 'races',
			'items' => [],
			'type' => 0,
			'page' => 0
		];

		$items = Statistic::query()
			->where('stat_type', 3)
			->where('stat_code', 1)
			->orderBy($this->field . '_rank')
			->get();

		foreach ($items as $item) {
			$row = [];
			$row['position'] = (int) $item->{$this->field . '_rank'};
			$row['race'] = (int) $item->race;
			$row['count'] = (int) $item->total_count;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;
		}

		return response()->state($parse);
	}
}
