<?php

namespace App\Http\Resources;

use App\Engine\Enums\ItemType;
use App\Engine\Game;
use App\Facades\Vars;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin User
 * @property User $resource
 */
class UserResource extends JsonResource
{
	public function toArray($request)
	{
		$quests = Cache::remember('app::quests::' . $this->resource->id, 3600, function () {
			return $this->resource->quests()->where('finish', 1)->count();
		});

		$planets = Cache::remember('app::planetlist_' . $this->resource->id, 600, function () {
			return $this->resource->getPlanets();
		});

		$data = [
			'id' => $this->resource->id,
			'name' => trim($this->resource->username),
			'race' => $this->resource->race,
			'sex' => $this->resource->sex,
			'avatar' => $this->resource->avatar,
			'messages' => $this->resource->messages,
			'alliance' => $this->resource->alliance_id > 0 ? [
				'id' => $this->resource->alliance_id,
				'name' => $this->resource->alliance_name,
				'messages' => $this->resource->messages_ally
			] : null,
			'planets' => UserPlanets::collection($planets),
			'queue_max' => config('game.maxBuildingQueue') + $this->resource->bonus('queue', 0),
			'vacation' => $this->resource->vacation?->utc()->toAtomString(),
			'quests' => (int) $quests,
			'links' => $this->resource->links,
			'credits' => $this->resource->credits,
			'options' => $this->resource->getOptions(),
			'officiers' => [],
			'technology' => [],
			'fleets_max' => $this->resource->getTechLevel('computer') + 1,
			'protection' => $this->resource->isNoobProtection(),
			'daily_bonus' => null,
			'deleted_at' => $this->resource->delete_time?->utc()->toAtomString(),
		];

		if ($this->resource->rpg_admiral?->isFuture()) {
			$data['fleets_max'] += 2;
		}

		foreach (Vars::getItemsByType(ItemType::OFFICIER) as $officier) {
			$data['officiers'][] = [
				'id' => $officier,
				'date' => $this->resource->{Vars::getName($officier)}?->isFuture() ? $this->resource->{Vars::getName($officier)}->utc()->toAtomString() : null,
			];
		}

		foreach (Vars::getItemsByType(ItemType::TECH) as $elementId) {
			$data['technology'][Vars::getName($elementId)] = $this->resource->getTechLevel($elementId);
		}

		$data['points'] = [
			'build' => 0,
			'tech' => 0,
			'fleet' => 0,
			'defs' => 0,
			'total' => 0,
			'place' => 0,
			'diff' => 0,
		];

		if ($points = $this->resource->getPoints()) {
			$data['points']['build'] = (int) $points->build_points;
			$data['points']['tech'] = (int) $points->tech_points;
			$data['points']['fleet'] = (int) $points->fleet_points;
			$data['points']['defs'] = (int) $points->defs_points;
			$data['points']['total'] = (int) $points->total_points;
			$data['points']['place'] = (int) $points->total_rank;
			$data['points']['diff'] = (int) ($points->total_old_rank ?: $points->total_rank) - (int) $points->total_rank;
		}

		$data['raids'] = [
			'win' => $this->resource->raids_win,
			'lost' => $this->resource->raids_lose,
			'total' => $this->resource->raids,
		];

		$data['lvl'] = [
			'mine' => [
				'p' => $this->resource->xpminier,
				'l' => $this->resource->lvl_minier,
				'u' => $this->resource->lvl_minier ** 3,
			],
			'raid' => [
				'p' => $this->resource->xpraid,
				'l' => $this->resource->lvl_raid,
				'u' => $this->resource->lvl_raid ** 2,
			],
		];

		if ($this->resource->daily_bonus->isPast()) {
			$bonusFactor = min(50, $this->resource->daily_bonus_factor + 1);

			if ($this->resource->daily_bonus->subDay()->isPast()) {
				$bonusFactor = 1;
			}

			$data['daily_bonus'] = $bonusFactor * 500 * Game::getSpeed('mine');
		}

		return $data;
	}
}
