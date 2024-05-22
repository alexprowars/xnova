<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use App\Models\UserQuest;
use App\Vars;

/**
 * @mixin \App\User
 */
class User extends JsonResource
{
	public function toArray($request)
	{
		$quests = Cache::remember('app::quests::' . $this->getId(), 3600, function () {
			return UserQuest::query()
				->where('user_id', $this->getId())
				->where('finish', 1)
				->count();
		});

		$planets = Cache::remember('app::planetlist_' . $this->getId(), 600, function () {
			return $this->getPlanets();
		});

		$data = [
			'id' => $this->id,
			'name' => trim($this->username),
			'race' => (int) $this->race,
			'messages' => $this->messages,
			'alliance' => $this->ally_id > 0 ? [
				'id' => $this->ally_id,
				'name' => $this->ally_name,
				'messages' => $this->messages_ally
			] : null,
			'planets' => UserPlanets::collection($planets),
			'timezone' => (int) $this->getOption('timezone'),
			'color' => (int) $this->getOption('color'),
			'vacation' => $this->vacation > 0,
			'quests' => (int) $quests,
			'credits' => $this->credits,
			'officiers' => [],
		];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$data['officiers'][] = [
				'id' => $officier,
				'time' => (int) $this->{Vars::getName($officier)}
			];
		}

		return $data;
	}
}
