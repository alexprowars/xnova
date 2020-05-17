<?php

namespace Xnova\Queue;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Xnova\Planet;
use Xnova\Queue;
use Xnova\Vars;
use Xnova\Models;
use Xnova\Entity;

class Tech
{
	private $_queue = null;

	public function __construct(Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add($elementId)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		$techHandle = Models\Queue::query()
			->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)
			->exists();

		if (!$techHandle) {
			if ($user->getTechLevel('intergalactic') > 0) {
				$planet->spaceLabs = $planet->getNetworkLevel();
			}

			$entity = new Entity\Research($elementId, $user->getTechLevel($elementId), new Entity\Context($user, $planet));
			$cost = $entity->getPrice();

			$price = Vars::getItemPrice($elementId);

			if ($entity->isAvailable() && $entity->canBuy($cost) && !(isset($price['max']) && $user->getTechLevel($elementId) >= $price['max'])) {
				$planet->metal 		-= $cost['metal'];
				$planet->crystal 	-= $cost['crystal'];
				$planet->deuterium 	-= $cost['deuterium'];
				$planet->update();

				$buildTime = $entity->getTime();

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

				if (Config::get('game.log.research', false) == true) {
					DB::table('log_history')->insert([
						'user_id' 			=> $user->getId(),
						'time' 				=> time(),
						'operation' 		=> 5,
						'planet' 			=> $planet->id,
						'from_metal' 		=> $planet->metal + $cost['metal'],
						'from_crystal' 		=> $planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
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

	public function delete($elementId)
	{
		$user = $this->_queue->getUser();

		/** @var Models\Queue $techHandle */
		$techHandle = Models\Queue::query()->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)->first();

		if ($techHandle && $techHandle->object_id == $elementId) {
			/** @var Planet $planet */
			$planet = Planet::query()->find((int) $techHandle->planet_id);

			$entity = new Entity\Research($elementId, $techHandle->level, new Entity\Context($user, $planet));

			$cost = $entity->getPrice();

			$planet->metal 		+= $cost['metal'];
			$planet->crystal 	+= $cost['crystal'];
			$planet->deuterium 	+= $cost['deuterium'];
			$planet->update();

			$techHandle->delete();
			$this->_queue->loadQueue();

			if (Config::get('game.log.research', false) == true) {
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
