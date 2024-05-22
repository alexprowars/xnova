<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use App\Controller;
use App\Game;
use App\Models\Statistic;

class StatController extends Controller
{
	private $field = '';
	private $page = 1;
	private $pid = 0;

	public function __construct()
	{
		parent::__construct();

		$this->page = (int) Request::input('range', 0);
		$this->page = max($this->page, 1);

		$this->pid = (int) Request::query('pid', 0);

		$type = (int) Request::input('type', 1);
		$view = Request::input('view', 'players');

		if ($view != 'players' && $type > 5) {
			$type = 1;
		}

		switch ($type) {
			case 2:
				$this->field = 'fleet';
				break;
			case 3:
				$this->field = 'tech';
				break;
			case 4:
				$this->field = 'defs';
				break;
			case 5:
				$this->field = 'build';
				break;
			case 6:
				$this->field = 'minier';
				break;
			case 7:
				$this->field = 'raid';
				break;
			default:
				$this->field = 'total';
		}

		$this->showTopPanel(false);
	}

	public function index()
	{
		$view = Request::input('view', 'players');

		switch ($view) {
			case 'alliances':
				return $this->alliances();
			case 'races':
				return $this->races();
			default:
				return $this->players();
		}
	}

	private function players()
	{
		$type = (int) Request::input('type', 1);

		$parse = [
			'update' => Game::datezone("d.m.Y - H:i:s", config('game.stat_update', 0)),
			'list' => 'players',
			'type' => $type,
			'page' => 1
		];

		if (!$this->page && Auth::check()) {
			$records = Cache::remember('app::records_' . $this->user->getId(), 1800, function () {
				$records = Statistic::query()
					->select(['build_points', 'tech_points', 'fleet_points', 'defs_points', 'total_points', 'total_old_rank', 'total_rank'])
					->where('stat_type', 1)
					->where('stat_code', 1)
					->where('id_owner', $this->user->getId())
					->first();

				return $records ? $records->toArray() : null;
			});

			if ($records) {
				$this->page = $records[$this->field . '_rank'];
			}
		}

		$users = (int) config('game.active_users', 0);

		$parse['elements'] = $users;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		if ($type == 6 || $type == 7) {
			$query = DB::select("SELECT u.username, u.race, u.id as id_owner, a.name as ally_name, u.ally_id as id_ally, u.lvl_" . $this->field . " as " . $this->field . "_points, 0 as " . $this->field . "_old_rank FROM users u LEFT JOIN alliances a ON a.id = u.ally_id WHERE 1 = 1 ORDER BY u.lvl_" . $this->field . " DESC, u.xp" . $this->field . " DESC LIMIT " . $position . ", 100");
		} else {
			$query = DB::select("SELECT s.*, u.username, u.race FROM statistics s LEFT JOIN users u ON u.id = s.id_owner WHERE s.stat_type = '1' AND s.stat_code = '1' AND s.stat_hide = 0 ORDER BY s." . $this->field . "_rank ASC LIMIT " . $position . ", 100");
		}

		$position++;

		$parse['items'] = [];

		foreach ($query as $item) {
			$row = [];
			$row['id'] = (int) $item->id_owner;
			$row['position'] = $position;

			$oldPosition = (int) $item->{$this->field . '_old_rank'};

			if ($oldPosition == 0) {
				$oldPosition = $position;
			}

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item->username;
			$row['name_marked'] = (Auth::check() && $item->id_owner == $this->user->id) || $item->id_owner == $this->pid;

			$row['alliance'] = false;

			if ($item->id_ally) {
				$row['alliance'] = [
					'id' => (int) $item->id_ally,
					'name' => $item->ally_name,
					'marked' => Auth::check() && $item->ally_name == $this->user->ally_name
				];
			}

			$row['race'] = (int) $item->race;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return $parse;
	}

	private function alliances()
	{
		$type = (int) Request::input('type', 1);

		$parse = [
			'update' => Game::datezone("d.m.Y - H:i:s", config('game.stat_update', 0)),
			'list' => Route::current()->getActionMethod(),
			'type' => $type,
			'page' => 1
		];

		$alliances = (int) config('game.active_alliance', 0);

		$parse['elements'] = $alliances;
		$parse['page'] = $this->page;

		$position = ($parse['page'] - 1) * 100;

		$query = DB::select("SELECT s.*, a.`id` as ally_id, a.`tag`, a.`name`, a.`members` FROM statistics s, alliances a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`" . $this->field . "_rank` ASC LIMIT " . $position . ",100;");

		$position++;

		$parse['items'] = [];

		foreach ($query as $item) {
			$row = [];
			$row['id'] = (int) $item->ally_id;
			$row['position'] = $position;

			$oldPosition = (int) $item->{$this->field . '_old_rank'};

			if ($oldPosition == 0) {
				$oldPosition = $position;
			}

			$row['diff'] = $oldPosition - $position;
			$row['name'] = $item->name;
			$row['name_marked'] = Auth::check() && $item->name == $this->user->ally_name;
			$row['members'] = (int) $item->members;
			$row['points'] = (int) $item->{$this->field . '_points'};

			$parse['items'][] = $row;

			$position++;
		}

		return $parse;
	}

	private function races()
	{
		$parse = [
			'update' => Game::datezone("d.m.Y - H:i:s", config('game.stat_update', 0)),
			'list' => Route::current()->getActionMethod(),
			'type' => 0,
			'page' => 0
		];

		$query = DB::select("SELECT * FROM statistics WHERE `stat_type` = 3 AND `stat_code` = 1 ORDER BY `" . $this->field . "_rank` ASC;");

		foreach ($query as $item) {
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
