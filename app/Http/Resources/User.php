<?php

namespace App\Http\Resources;

use App\Engine\Enums\ItemType;
use App\Engine\Vars;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin \App\Models\User
 */
class User extends JsonResource
{
	public function toArray($request)
	{
		$quests = Cache::remember('app::quests::' . $this->id, 3600, function () {
			return $this->quests()->where('finish', 1)->count();
		});

		$planets = Cache::remember('app::planetlist_' . $this->id, 600, function () {
			return $this->getPlanets();
		});

		$data = [
			'id' => $this->id,
			'name' => trim($this->username),
			'race' => $this->race,
			'sex' => $this->sex,
			'avatar' => $this->avatar,
			'messages' => $this->messages,
			'alliance' => $this->alliance_id > 0 ? [
				'id' => $this->alliance_id,
				'name' => $this->alliance_name,
				'messages' => $this->messages_ally
			] : null,
			'planets' => UserPlanets::collection($planets),
			'queue_max' => config('game.maxBuildingQueue') + $this->bonus('queue', 0),
			'vacation' => $this->vacation?->utc()->toAtomString(),
			'quests' => (int) $quests,
			'credits' => $this->credits,
			'options' => $this->getOptions(),
			'officiers' => [],
			'technology' => [],
			'fleets_max' => $this->getTechLevel('computer') + 1,
		];

		if ($this->rpg_admiral?->isFuture()) {
			$data['fleets_max'] += 2;
		}

		foreach (Vars::getItemsByType(ItemType::OFFICIER) as $officier) {
			$data['officiers'][] = [
				'id' => $officier,
				'time' => $this->{Vars::getName($officier)}->isFuture() ? $this->{Vars::getName($officier)}?->utc()->toAtomString() : null,
			];
		}

		foreach (Vars::getItemsByType(ItemType::TECH) as $elementId) {
			$data['technology'][Vars::getName($elementId)] = $this->getTechLevel($elementId);
		}

		return $data;
	}
}
