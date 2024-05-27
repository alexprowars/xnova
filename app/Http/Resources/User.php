<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use App\Models\UserQuest;
use App\Vars;

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
			'race' => (int) $this->race,
			'sex' => (int) $this->sex,
			'avatar' => $this->avatar,
			'messages' => $this->messages,
			'alliance' => $this->alliance_id > 0 ? [
				'id' => $this->alliance_id,
				'name' => $this->alliance_name,
				'messages' => $this->messages_ally
			] : null,
			'planets' => UserPlanets::collection($planets),
			'vacation' => $this->vacation?->format('c'),
			'quests' => (int) $quests,
			'credits' => $this->credits,
			'options' => $this->getOptions(),
			'officiers' => [],
		];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$data['officiers'][] = [
				'id' => $officier,
				'time' => (int) $this->{Vars::getName($officier)}?->timestamp,
			];
		}

		return $data;
	}
}
