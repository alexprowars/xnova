<?php

namespace Xnova\Queue;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Xnova\Building;
use Xnova\Queue;
use Xnova\Vars;
use Xnova\Models;

class Unit
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId, $count)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		if (!Building::isTechnologieAccessible($user, $planet, $elementId))
			return;

		$buildItems = $this->_queue->get(Queue::TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503)
		{
			$Missiles = [];
			$Missiles[502] = $planet->getUnitCount('interceptor_misil');
			$Missiles[503] = $planet->getUnitCount('interplanetary_misil');

			$maxMissiles = $planet->getBuildLevel('missile_facility') * 10;

			foreach ($buildItems AS $item)
			{
				if (($item->object_id == 502 || $item->object_id == 503) && $item->level != 0)
					$Missiles[$item->object_id] += $item->level;
			}
		}

		$price = Vars::getItemPrice($elementId);

		if (isset($price['max']))
		{
			$total = $planet->getUnitCount($elementId);

			foreach ($buildItems AS $item)
			{
				if ($item->object_id == $elementId)
					$total += $item->level;
			}

			$count = min($count, max(($price['max'] - $total), 0));
		}

		if (($elementId == 502 || $elementId == 503) && isset($Missiles) && isset($maxMissiles))
		{
			$ActuMissiles 	= $Missiles[502] + (2 * $Missiles[503]);
			$MissilesSpace 	= $maxMissiles - $ActuMissiles;

			if ($MissilesSpace > 0)
			{
				if ($elementId == 502)
					$count = min($count, $MissilesSpace);
				else
					$count = min($count, floor($MissilesSpace / 2));
			}
			else
				$count = 0;
		}

		if (!$count)
			return;

		$count = min($count, Building::getMaxConstructibleElements($elementId, $planet, $user));

		if ($count > 0)
		{
			$cost = Building::getElementRessources($elementId, $count, $user);

			$planet->metal 		-= $cost['metal'];
			$planet->crystal 	-= $cost['crystal'];
			$planet->deuterium 	-= $cost['deuterium'];
			$planet->update();

			$buildTime = Building::getBuildingTime($user, $planet, $elementId);

			Models\Queue::query()->create([
				'type' => Models\Queue::TYPE_UNIT,
				'operation' => Models\Queue::OPERATION_BUILD,
				'user_id' => $user->getId(),
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => time(),
				'time_end' => $buildTime + time(),
				'level' => $count
			]);

			if (Config::get('game.log.units', false) == true)
			{
				DB::table('log_history')->insert([
					'user_id' 			=> $user->id,
					'time' 				=> time(),
					'operation' 		=> 7,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal + $cost['metal'],
					'from_crystal' 		=> $planet->crystal + $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'build_id' 			=> $elementId,
					'count' 			=> $count
				]);
			}
		}
	}
}