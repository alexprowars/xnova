<?php

namespace App\Http\Controllers;

use App\Models\Statistic;
use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
	private $field;
	private $page;

	public function __construct(Request $request)
	{
		parent::__construct();

		$this->page = (int) $request->input('page', 0);
		$this->page = max($this->page, 1);

		$type = (int) $request->input('type', 1);
		$view = $request->input('view', 'players');

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

	public function index(Settings $settings, Request $request)
	{
		$type = (int) $request->input('type', 1);

		$parse = [
			'update' => Date::createFromTimestamp($settings->statUpdate, config('app.timezone'))->utc()->toAtomString(),
			'list' => 'players',
			'type' => $type,
			'page' => $this->page,
		];

		if (!$this->page && $points = $this->user?->getPoints()) {
			$this->page = $points->{$this->field . '_rank'};
		}

		$parse['elements'] = $settings->activeUsers;

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
					'marked' => $item->alliance_name == $this->user?->alliance_name,
				];
			}

			$row['race'] = (int) $item->race;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return $parse;
	}

	public function alliances(Settings $settings, Request $request)
	{
		$type = (int) $request->input('type', 1);

		$parse = [
			'update' => Date::createFromTimestamp($settings->statUpdate, config('app.timezone'))->utc()->toAtomString(),
			'list' => 'alliances',
			'type' => $type,
			'page' => $this->page,
		];

		$parse['elements'] = $settings->activeAlliance;

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
			$row['name_marked'] = $item->name == $this->user?->alliance_name;
			$row['members'] = (int) $item->members_count;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return $parse;
	}

	public function races(Settings $settings)
	{
		$parse = [
			'update' => Date::createFromTimestamp($settings->statUpdate, config('app.timezone'))->utc()->toAtomString(),
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

		return $parse;
	}
}
