<?php

namespace App\Http\Controllers;

use App\Engine\Enums\PlanetType;
use App\Engine\Formulas;
use App\Models;
use App\Models\AllianceDiplomacy;
use Illuminate\Http\Request;

class GalaxyController extends Controller
{
	public function index(Request $request)
	{
		$maxfleet_count = Models\Fleet::query()
			->whereBelongsTo($this->user)
			->count();

		$galaxy = $this->planet->galaxy;
		$system = $this->planet->system;

		if ($request->input('galaxy')) {
			$galaxy = (int) $request->input('galaxy', 1);
		}

		if ($request->input('system')) {
			$system = (int) $request->input('system', 1);
		}

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));

		$phalanx = false;

		if ($this->planet->getLevel('phalanx') > 0) {
			$range = Formulas::getPhalanxRange($this->planet->getLevel('phalanx'));

			$systemLimitMin = max(1, $this->planet->system - $range);
			$systemLimitMax = $this->planet->system + $range;

			if ($system <= $systemLimitMax && $system >= $systemLimitMin) {
				$phalanx = true;
			}
		}

		if ($this->planet->getLevel('interplanetary_misil') > 0 && $galaxy == $this->planet->galaxy) {
			$range = Formulas::getMissileRange($this->user->getTechLevel('impulse_motor'));

			$systemLimitMin = max(1, $this->planet->system - $range);
			$systemLimitMax = $this->planet->system + $range;

			if ($system <= $systemLimitMax) {
				$missileBtn = $system >= $systemLimitMin;
			} else {
				$missileBtn = false;
			}
		} else {
			$missileBtn = false;
		}

		$jsUser = [
			'phalanx' => $phalanx,
			'missile' => $missileBtn,
			'fleets' => $maxfleet_count,
		];

		$parse = [];
		$parse['galaxy'] = (int) $galaxy;
		$parse['galaxy_max'] = (int) config('game.maxGalaxyInWorld');
		$parse['system'] = (int) $system;
		$parse['system_max'] = (int) config('game.maxSystemInGalaxy');
		$parse['user'] = $jsUser;
		$parse['items'] = [];
		$parse['shortcuts'] = [];

		$planets = $this->user->getPlanets(false);

		foreach ($planets as $planet) {
			$parse['shortcuts'][] = $planet->only(['name', 'galaxy', 'system', 'planet']);
		}

		foreach ($this->user->shortcuts as $shortcut) {
			$parse['shortcuts'][] = $shortcut->only(['name', 'galaxy', 'system', 'planet']);
		}

		$items = Models\Planet::query()
			->with(['moon', 'user', 'user.alliance', 'user.statistics', 'user.roles'])
			->where('galaxy', $galaxy)
			->where('system', $system)
			->whereNot('planet_type', PlanetType::MOON)
			->get();

		$diplomacyItems = collect();

		if ($this->user->alliance) {
			$allyIds = $items->pluck('user.alliance_id')->filter();

			if ($allyIds->isNotEmpty()) {
				$diplomacyItems = Models\AllianceDiplomacy::query()
					->where('status', 1)
					->whereIn('alliance_id', $allyIds)
					->whereBelongsTo($this->user->alliance, 'diplomacy')
					->get()->keyBy('alliance_id');
			}
		}

		foreach ($items as $item) {
			$activeTime = $item->last_active;

			if ($item->moon && $item->moon->last_active && $item->moon->last_active->timestamp > $activeTime->timestamp) {
				$activeTime = $item->moon->last_active;
			}

			if ($item->destroyed_at && $item->destroyed_at->isPast()) {
				$item->delete();

				if ($item->moon) {
					$item->moon->delete();
				}
			}

			if ($item->moon && $item->moon->destroyed_at && $item->moon->destroyed_at->isPast()) {
				$item->moon->delete();
				$item->update(['moon_id' => null]);
				$item->unsetRelation('moon');
			}

			if ($activeTime?->timestamp > time() - 59 * 60) {
				$planetActive = floor((time() - $activeTime->timestamp) / 60);
			} else {
				$planetActive = 60;
			}

			$row = [
				'id' => $item->id,
				'position' => [
					'galaxy' => $item->galaxy,
					'system' => $item->system,
					'planet' => $item->planet,
				],
				'planet' => [
					'id' => $item->id,
					'name' => $item->name,
					'type' => $item->planet_type->value,
					'image' => $item->image,
					'active' => $planetActive,
					'destruyed' => $item->destroyed_at?->utc()->toAtomString(),
				],
				'debris' => [
					'metal' => $item->debris_metal,
					'crystal' => $item->debris_crystal,
				],
				'moon' => null,
				'user' => null,
				'alliance' => null,
			];

			if ($moon = $item->moon) {
				$row['moon'] = [
					'id' => $moon->id,
					'name' => $moon->name,
					'destruyed' => $moon->destroyed_at?->utc()->toAtomString(),
					'diameter' => $moon->diameter,
					'temp' => $moon->temp_min,
				];
			}

			if ($user = $item->user) {
				$onlineDiff = $user->onlinetime->diffInDays();

				if ($onlineDiff >= 28) {
					$userOnline = 2;
				} elseif ($onlineDiff >= 7) {
					$userOnline = 1;
				} else {
					$userOnline = 0;
				}

				$image = $user->getFirstMediaUrl(conversionName: 'thumb') ?: null;

				$row['user'] = [
					'id' => $user->id,
					'name' => $user->username,
					'race' => $user->race,
					'role' => $user->roles->first()->name ?? null,
					'online' => $userOnline,
					'vacation' => $user->isVacation(),
					'blocked' => $user->blocked_at?->utc()->toAtomString(),
					'sex' => $user->sex,
					'avatar' => $user->avatar,
					'image' => $image,
					'stats' => null,
				];

				if ($stats = $user->statistics) {
					$row['user']['stats'] = [
						'rank' => $stats->total_rank,
						'points' => $stats->total_points,
					];
				}

				if ($alliance = $user->alliance) {
					/** @var AllianceDiplomacy|null $diplomacy */
					$diplomacy = $diplomacyItems->get($alliance->id);

					$row['alliance'] = [
						'id' => $alliance->id,
						'name' => $alliance->name,
						'members' => $alliance->members_count,
						'web' => $alliance->web,
						'tag' => $alliance->tag,
						'diplomacy' => $diplomacy?->type,
					];
				}
			}

			$parse['items'][] = $row;
		}

		return $parse;
	}
}
