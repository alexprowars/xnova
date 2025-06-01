<?php

namespace App\Models;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use Illuminate\Database\Eloquent\Model;

class UserQuest extends Model
{
	protected $guarded = false;
	public $table = 'users_quests';

	public function checkFinished(User $user, Planet $planet)
	{
		$tutorial = require resource_path('engine/tutorial.php');

		if (!isset($tutorial[$this->quest_id])) {
			return [];
		}

		$result = [];

		foreach ($tutorial[$this->quest_id]['task'] as $taskKey => $taskVal) {
			if ($taskKey == 'build') {
				$chk = true;

				foreach ($taskVal as $element => $level) {
					$type = Vars::getItemType($element);

					if ($type == ItemType::TECH) {
						$check = $user->getTechLevel($element) >= $level;
					} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
						$check = $planet->getLevel($element) >= $level;
					} else {
						$check = $planet->getLevel($element) >= $level;
					}

					if ($chk) {
						$chk = $check;
					}
				}

				$result[$taskKey] = $chk;
			}

			if ($taskKey == '!planet_name') {
				$result[$taskKey] = $planet->name != $taskVal;
			}

			if ($taskKey == 'friends_count') {
				$count = Friend::query()->whereBelongsTo($user)->orWhereBelongsTo($user, 'friend')->count();

				$result[$taskKey] = $count >= $taskVal;
			}

			if ($taskKey == 'ally') {
				$result[$taskKey] = $user->alliance_id > 0;
			}

			if ($taskKey == 'storage' && $taskVal === true) {
				$result[$taskKey] = $planet->getLevel('metal_store') > 0 || $planet->getLevel('crystal_store') > 0 || $planet->getLevel('deuterium_store') > 0;
			}

			if ($taskKey == 'trade') {
				$result[$taskKey] = $this->stage > 0;
			}

			if ($taskKey == 'fleet_mission') {
				$result[$taskKey] = $this->stage > 0;
			}

			if ($taskKey == 'planets') {
				$count = $user->planets()
					->where('planet_type', PlanetType::PLANET)
					->count();

				$result[$taskKey] = $count >= $taskVal;
			}
		}

		return $result;
	}
}
