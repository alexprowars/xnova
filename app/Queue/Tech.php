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
use Xnova\Planet;
use Xnova\Queue;
use Xnova\Vars;
use Xnova\Models;

class Tech
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		$techHandle = Models\Queue::query()
			->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)
			->exists();

		if (!$techHandle)
		{
			if ($user->getTechLevel('intergalactic') > 0)
				$planet->spaceLabs = $planet->getNetworkLevel();

			$price = Vars::getItemPrice($elementId);

			if (Building::isTechnologieAccessible($user, $planet, $elementId) && Building::isElementBuyable($user, $planet, $elementId) && !(isset($price['max']) && $user->getTechLevel($elementId) >= $price['max']))
			{
				$costs = Building::getBuildingPrice($user, $planet, $elementId);

				$planet->metal 		-= $costs['metal'];
				$planet->crystal 	-= $costs['crystal'];
				$planet->deuterium 	-= $costs['deuterium'];
				$planet->update();

				$buildTime = Building::getBuildingTime($user, $planet, $elementId);

				Models\Queue::query()->create([
					'type' => Models\Queue::TYPE_TECH,
					'operation' => Models\Queue::OPERATION_BUILD,
					'user_id' => $user->getId(),
					'planet_id' => $planet->id,
					'object_id' => $elementId,
					'time' => time(),
					'time_end' => time() + $buildTime,
					'level' => $user->getTechLevel($elementId) + 1
				]);

				if (Config::get('game.log.research', false) == true)
				{
					DB::table('log_history')->insert([
						'user_id' 			=> $user->getId(),
						'time' 				=> time(),
						'operation' 		=> 5,
						'planet' 			=> $planet->id,
						'from_metal' 		=> $planet->metal + $costs['metal'],
						'from_crystal' 		=> $planet->crystal + $costs['crystal'],
						'from_deuterium' 	=> $planet->deuterium + $costs['deuterium'],
						'to_metal' 			=> $planet->metal,
						'to_crystal' 		=> $planet->crystal,
						'to_deuterium' 		=> $planet->deuterium,
						'build_id' 			=> $elementId,
						'level' 			=> $user->getTechLevel($elementId) + 1
					]);
				}
			}
		}
	}

	public function delete ($elementId)
	{
		$user = $this->_queue->getUser();

		$techHandle = Models\Queue::query()->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)->first();

		if ($techHandle && $techHandle->object_id == $elementId)
		{
			/** @var Planet $planet */
			$planet = Planet::query()->find((int) $techHandle->planet_id);

			$cost = Building::getBuildingPrice($user, $planet, $elementId);

			$planet->metal 		+= $cost['metal'];
			$planet->crystal 	+= $cost['crystal'];
			$planet->deuterium 	+= $cost['deuterium'];
			$planet->update();

			$techHandle->delete();
			$this->_queue->loadQueue();

			if (Config::get('game.log.research', false) == true)
			{
				DB::table('log_history')->insert([
					'user_id' 			=> $user->getId(),
					'time' 				=> time(),
					'operation' 		=> 6,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal - $cost['metal'],
					'from_crystal' 		=> $planet->crystal - $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium - $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'build_id' 			=> $elementId,
					'level' 			=> $user->getTechLevel($elementId) + 1
				]);
			}
		}
	}
}