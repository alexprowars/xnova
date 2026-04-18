<?php

namespace App\Http\Controllers;

use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Exceptions\Exception;
use App\Models\LogsStat;
use App\Models\Planet;
use App\Models\Statistic;
use App\Models\User;

class PlayersController extends Controller
{
	public function index(int $userId): array
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception('Профиль не найден');
		}

		$result = [
			'id' => $user->id,
			'avatar' => '/images/no_photo.gif',
			'sex' => $user->sex,
			'name' => $user->username,
			'race' => $user->race,
			'alliance' => null,
			'planet' => null,
			'fights' => [
				'wons' => $user->raids_win,
				'loos' => $user->raids_lose,
				'total' => $user->raids,
			],
			'about' => preg_replace('/(\r\n)/u', '<br>', stripslashes($user->about)),
			'level' => [
				'mine' => User::getRankId($user->lvl_minier),
				'raid' => User::getRankId($user->lvl_raid),
			],
		];

		if ($file = $user->getFirstMediaUrl(conversionName: 'thumb')) {
			$result['avatar'] = $file;
		} elseif ($user->avatar) {
			if ($user->avatar != 99) {
				$result['avatar'] = '/images/faces/' . $user->sex . '/' . $user->avatar . '.png';
			}
		}

		$planet = Planet::findByCoordinates(new Coordinates($user->galaxy, $user->system, $user->planet, PlanetType::PLANET));

		if ($planet) {
			$result['planet'] = [
				'name' => $planet->name,
				'galaxy' => $planet->galaxy,
				'system' => $planet->system,
				'planet' => $planet->planet,
			];
		}

		if ($user->alliance) {
			$result['alliance'] = [
				'id' => $user->alliance->id,
				'name' => $user->alliance->name,
			];
		}

		$result['stats'] = null;

		$points = Statistic::query()
			->where('stat_type', 1)
			->where('stat_code', 1)
			->whereBelongsTo($user)
			->first();

		if ($points) {
			$result['stats'] = [
				'tech_rank' => $points->tech_rank,
				'tech_points' => $points->tech_points,
				'build_rank' => $points->build_rank,
				'build_points' => $points->build_points,
				'fleet_rank' => $points->fleet_rank,
				'fleet_points' => $points->fleet_points,
				'defs_rank' => $points->defs_rank,
				'defs_points' => $points->defs_points,
				'total_rank' => $points->total_rank,
				'total_points' => $points->total_points,
			];
		}

		return $result;
	}

	public function stats(int $id): array
	{
		$player = User::find($id);

		if (!$player) {
			throw new Exception('Информация о данном игроке не найдена');
		}

		$result = [
			'name' => $player->username,
			'points' => [],
		];

		$items = LogsStat::query()->where('object_id', $id)
			->where('type', 1)
			->where('date', '>', now()->subDays(14))
			->orderBy('date')
			->get();

		foreach ($items as $item) {
			$result['points'][] = [
				'date' => $item->date->utc()->toAtomString(),
				'rank' => [
					'tech' => $item->tech_rank,
					'build' => $item->build_rank,
					'defs' => $item->defs_rank,
					'fleet' => $item->fleet_rank,
					'total' => $item->total_rank,
				],
				'point' => [
					'tech' => $item->tech_points,
					'build' => $item->build_points,
					'defs' => $item->defs_points,
					'fleet' => $item->fleet_points,
					'total' => $item->total_points,
				]
			];
		}

		return $result;
	}
}
