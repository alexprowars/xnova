<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Files;
use App\Models\LogStat;
use App\Models\Planet;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class PlayersController extends Controller
{
	public function index(int $userId)
	{
		if (!$userId) {
			throw new PageException('Профиль не найден');
		}

		$user = User::find($userId);

		if (!$user) {
			throw new PageException('Профиль не найден');
		}

		$parse = [];
		$parse['avatar'] = '/images/no_photo.gif';

		if ($user->image) {
			$file = Files::getById($user->image);

			if ($file) {
				$parse['avatar'] = URL::asset($file['src']);
			}
		} elseif ($user->avatar) {
			if ($user->avatar != 99) {
				$parse['avatar'] = '/images/faces/' . $user->sex . '/' . $user->avatar . '.png';
			}
		}

		$parse['userplanet'] = '';

		$planet = Planet::findByCoordinates(new Coordinates($user->galaxy, $user->system, $user->planet, 1));

		if ($planet) {
			$parse['userplanet'] = $planet->name;
		}

		$parse['stats'] = false;

		$points = Statistic::query()
			->where('stat_type', 1)
			->where('stat_code', 1)
			->where('user_id', $user->id)
			->first();

		if ($points) {
			$parse['stats'] = [
				'tech_rank' => (int) ($points->tech_rank ?? 0),
				'tech_points' => (int) ($points->tech_points ?? 0),
				'build_rank' => (int) ($points->build_rank ?? 0),
				'build_points' => (int) ($points->build_points ?? 0),
				'fleet_rank' => (int) ($points->fleet_rank ?? 0),
				'fleet_points' => (int) ($points->fleet_points ?? 0),
				'defs_rank' => (int) ($points->defs_rank ?? 0),
				'defs_points' => (int) ($points->defs_points ?? 0),
				'total_rank' => (int) ($points->total_rank ?? 0),
				'total_points' => (int) ($points->total_points ?? 0),
			];
		}

		$parse['sex'] = (int) $user->sex;
		$parse['id'] = (int) $user->id;
		$parse['username'] = $user->username;
		$parse['race'] = (int) $user->race;
		$parse['galaxy'] = (int) $user->galaxy;
		$parse['system'] = (int) $user->system;
		$parse['planet'] = (int) $user->planet;
		$parse['ally_id'] = (int) $user->alliance_id;
		$parse['ally_name'] = $user->alliance_name;
		$parse['about'] = preg_replace("/(\r\n)/u", "<br>", stripslashes($user->about));
		$parse['wons'] = (int) $user->raids_win;
		$parse['loos'] = (int) $user->raids_lose;
		$parse['total'] = (int) $user->raids;

		$parse['m'] = User::getRankId($user->lvl_minier);
		$parse['f'] = User::getRankId($user->lvl_raid);

		return response()->state($parse);
	}

	public function stat($userId)
	{
		if (!Auth::check()) {
			throw new PageException('Доступ запрещен');
		}

		$player = User::find($userId);

		if (!$player) {
			throw new ErrorException('Информация о данном игроке не найдена');
		}

		$parse = [];
		$parse['name'] = $player->username;
		$parse['points'] = [];

		$items = LogStat::query()->where('object_id', $userId)
			->where('type', 1)
			->where('time', '>', now()->subDays(14))
			->orderBy('time')
			->get();

		foreach ($items as $item) {
			$parse['points'][] = [
				'date' => $item->time?->utc()->toAtomString(),
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

		return response()->state($parse);
	}
}
