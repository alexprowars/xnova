<?php

namespace App\Listeners;

use App\Events\FleetSended;

class QuestsListeners
{
	public function handleFleets(FleetSended $event): void
	{
		$quest = $event->fleet->user->quests()
			->where('finish', false)
			->where('stage', 0)
			->first();

		if ($quest) {
			$quest = require resource_path('engine/tutorial.php');
			$quest = $quest[$quest->quest_id] ?? null;

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
