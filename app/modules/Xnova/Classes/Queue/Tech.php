<?php

namespace Xnova\Queue;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Building;
use Phalcon\Di;
use Xnova\Queue;

class Tech
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId)
	{
		$registry = Di::getDefault()->getShared('registry');
		
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();
		
		$TechHandle = $this->_queue->checkTechQueue();

		if (!$TechHandle['working'])
		{
			$spaceLabs = [];

			if ($user->{$registry->resource[123]} > 0)
				$spaceLabs = $planet->getNetworkLevel();

			$planet->spaceLabs = $spaceLabs;

			if (Building::IsTechnologieAccessible($user, $planet, $elementId) && Building::IsElementBuyable($user, $planet, $elementId) && !(isset($registry->pricelist[$elementId]['max']) && $user->{$registry->resource[$elementId]} >= $registry->pricelist[$elementId]['max']))
			{
				$costs = Building::GetBuildingPrice($user, $planet, $elementId);

				$planet->metal 		-= $costs['metal'];
				$planet->crystal 	-= $costs['crystal'];
				$planet->deuterium 	-= $costs['deuterium'];

				$time = Building::GetBuildingTime($user, $planet, $elementId);

				$this->_queue->set(Queue::QUEUE_TYPE_RESEARCH, [
					'i' => $elementId,
					'l' => ($user->{$registry->resource[$elementId]} + 1),
					't' => $time,
					's' => time(),
					'e' => time() + $time,
					'd' => 0
				]);

				$this->_queue->saveQueue();

				$user->update(['b_tech_planet' => $planet->id]);
			}
		}
	}

	public function delete ($elementId, $listId = 0)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		$TechHandle = $this->_queue->checkTechQueue();

		$queue = $this->_queue->get();

		if (isset($queue[Queue::QUEUE_TYPE_RESEARCH][$listId]) && $TechHandle['working'] && $queue[Queue::QUEUE_TYPE_RESEARCH][$listId]['i'] == $elementId)
		{
			$nedeed = Building::GetBuildingPrice($user, $TechHandle['planet'], $elementId);

			$TechHandle['planet']->metal 		+= $nedeed['metal'];
			$TechHandle['planet']->crystal 		+= $nedeed['crystal'];
			$TechHandle['planet']->deuterium 	+= $nedeed['deuterium'];

			unset($queue[Queue::QUEUE_TYPE_RESEARCH][$listId]);

			if (isset($queue[Queue::QUEUE_TYPE_BUILDING]) && !count($queue[Queue::QUEUE_TYPE_BUILDING]))
				unset($queue[Queue::QUEUE_TYPE_BUILDING]);

			$TechHandle['planet']->queue = json_encode($queue);
			$TechHandle['planet']->update();

			$user->update(['b_tech_planet' => $planet->id]);
		}
	}
}