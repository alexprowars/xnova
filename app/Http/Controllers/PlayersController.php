<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Files;
use Xnova\Models\Planets;
use Xnova\Controller;
use Xnova\User;

class PlayersController extends Controller
{
	public function index($userId)
	{
		$parse = [];

		if (!$userId) {
			throw new PageException('Профиль не найден');
		}

		$user = DB::selectOne("SELECT u.*, ui.about, ui.image FROM users u LEFT JOIN users_info ui ON ui.id = u.id WHERE u.id = '" . $userId . "'");

		if (!$user) {
			throw new PageException('Профиль не найден');
		}

		$parse['avatar'] = 'images/no_photo.gif';

		if ($user->image > 0) {
			$file = Files::getById($user->image);

			if ($file) {
				$parse['avatar'] = $file['src'];
			}
		} elseif ($user->avatar != 0) {
			if ($user->avatar != 99) {
				$parse['avatar'] = "images/faces/" . $user->sex . "/" . $user->avatar . ".png";
			}
		}

		$parse['avatar'] = URL::asset($parse['avatar']);
		$parse['userplanet'] = '';

		$planet = Planets::findByCoords($user->galaxy, $user->system, $user->planet, 1);

		if ($planet) {
			$parse['userplanet'] = $planet->name;
		}

		$parse['stats'] = false;

		$points = DB::selectOne("SELECT * FROM statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $user->id . "'");

		if ($points) {
			$parse['stats'] = [
				'tech_rank' => (int) $points->tech_rank ?? 0,
				'tech_points' => (int) $points->tech_points ?? 0,
				'build_rank' => (int) $points->build_rank ?? 0,
				'build_points' => (int) $points->build_points ?? 0,
				'fleet_rank' => (int) $points->fleet_rank ?? 0,
				'fleet_points' => (int) $points->fleet_points ?? 0,
				'defs_rank' => (int) $points->defs_rank ?? 0,
				'defs_points' => (int) $points->defs_points ?? 0,
				'total_rank' => (int) $points->total_rank ?? 0,
				'total_points' => (int) $points->total_points ?? 0,
			];
		}

		$parse['sex'] = (int) $user->sex;
		$parse['id'] = (int) $user->id;
		$parse['username'] = $user->username;
		$parse['race'] = (int) $user->race;
		$parse['galaxy'] = (int) $user->galaxy;
		$parse['system'] = (int) $user->system;
		$parse['planet'] = (int) $user->planet;
		$parse['ally_id'] = (int) $user->ally_id;
		$parse['ally_name'] = $user->ally_name;
		$parse['about'] = preg_replace("/(\r\n)/u", "<br>", stripslashes($user->about));
		$parse['wons'] = (int) $user->raids_win;
		$parse['loos'] = (int) $user->raids_lose;
		$parse['total'] = (int) $user->raids;

		$parse['m'] = User::getRankId($user->lvl_minier);
		$parse['f'] = User::getRankId($user->lvl_raid);

		$this->setTitle('Информация о игроке');
		$this->showTopPanel(false);
		$this->showLeftPanel(Auth::check());

		return $parse;
	}

	public function stat($userId)
	{
		if (!Auth::check()) {
			throw new PageException('Доступ запрещен');
		}

		$player = DB::selectOne("SELECT id, username FROM users WHERE id = " . $userId . "");

		if (!$player) {
			throw new ErrorException('Информация о данном игроке не найдена');
		}

		$parse = [];
		$parse['name'] = $player->username;
		$parse['points'] = [];

		$items = DB::select("SELECT * FROM log_stats WHERE object_id = " . $userId . " AND type = 1 AND time > " . (time() - 14 * 86400) . " ORDER BY time ASC");

		foreach ($items as $item) {
			$parse['points'][] = [
				'date' => (int) $item->time,
				'rank' => [
					'tech' => (int) $item->tech_rank,
					'build' => (int) $item->build_rank,
					'defs' => (int) $item->defs_rank,
					'fleet' => (int) $item->fleet_rank,
					'total' => (int) $item->total_rank,
				],
				'point' => [
					'tech' => (int) $item->tech_points,
					'build' => (int) $item->build_points,
					'defs' => (int) $item->defs_points,
					'fleet' => (int) $item->fleet_points,
					'total' => (int) $item->total_points,
				]
			];
		}

		$this->setTitle('Статистика игрока');
		$this->showTopPanel(false);
		$this->showLeftPanel(Auth::check());

		return $parse;
	}
}
