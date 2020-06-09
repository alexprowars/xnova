<?php

namespace Xnova\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Xnova\Models\UserQuest;
use Xnova\Vars;

/**
 * @mixin \Xnova\User
 */
class User extends JsonResource
{
	public function toArray($request)
	{
		$quests = Cache::remember('app::quests::' . $this->getId(), 3600, function () {
			return (int) UserQuest::query()
				->where('user_id', $this->getId())
				->where('finish', 1)
				->count();
		});

		$planets = Cache::remember('app::planetlist_' . $this->getId(), 600, function () {
			return $this->getPlanets();
		});

		$data = [
			'id' => (int) $this->id,
			'name' => trim($this->username),
			'race' => (int) $this->race,
			'messages' => (int) $this->messages,
			'alliance' => $this->ally_id > 0 ? [
				'id' => (int) $this->ally_id,
				'name' => $this->ally_name,
				'messages' => (int) $this->messages_ally
			] : null,
			'planets' => UserPlanets::collection($planets),
			'timezone' => (int) $this->getUserOption('timezone'),
			'color' => (int) $this->getUserOption('color'),
			'vacation' => $this->vacation > 0,
			'quests' => (int) $quests,
			'credits' => (int) $this->credits,
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
