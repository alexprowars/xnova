<?php

namespace App\Listeners;

use App\Events\FleetSended;

class QuestsListeners
{
	public function handleFleets(FleetSended $event): void
	{
		$questItem = $event->fleet->user->quests()
			->where('finish', false)
			->where('stage', 0)
			->first();

		if ($questItem) {
			$quest = require resource_path('engine/quests.php');
			$quest = $quest[$questItem->quest_id] ?? null;

			if ($quest) {
				foreach ($quest['task'] as $key => $value) {
					if ($key == 'fleet_mission' && $value == $event->fleet->mission) {
						$quest->update(['stage' => 1]);
					}
				}
			}
		}
	}
}
