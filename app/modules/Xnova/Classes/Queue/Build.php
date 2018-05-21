<?php

namespace Xnova\Queue;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Building;
use Phalcon\Di;
use Xnova\Queue;
use Xnova\Models;

class Build
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId, $destroy = false)
	{
		$config = Di::getDefault()->getShared('config');
		
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();
		
		$maxBuidSize = $config->game->maxBuildingQueue;

		if ($user->rpg_constructeur > time())
			$maxBuidSize += 2;

		$actualCount = $this->_queue->getCount(Queue::TYPE_BUILDING);

		if ($actualCount < $maxBuidSize)
			$queueID = $actualCount + 1;
		else
			$queueID = false;

		$currentMaxFields = $planet->getMaxFields();

		if ($planet->field_current < ($currentMaxFields - $actualCount) || $destroy)
		{
			if ($queueID > 1)
			{
				$inArray = 0;

				foreach ($this->_queue->get(Queue::TYPE_BUILDING) as $item)
				{
					if ($item->object_id == $elementId)
						$inArray++;
				}
			}
			else
				$inArray = 0;

			$build = $planet->getBuild($elementId);

			if (!$build)
				return false;

			$item = new Models\Queue();

			$item->create([
				'type' => $item::TYPE_BUILD,
				'operation' => $destroy ? $item::OPERATION_DESTROY : $item::OPERATION_BUILD,
				'user_id' => $user->getId(),
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => 0,
				'time_end' => 0,
				'level' => $build['level'] + (!$destroy ? 1 : 0) + $inArray
			]);

			$this->_queue->loadQueue();
			$this->_queue->nextBuildingQueue();
		}

		return true;
	}

	public function delete ($indexId)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		if ($this->_queue->getCount(Queue::TYPE_BUILDING))
		{
			$queueArray = $this->_queue->get(Queue::TYPE_BUILDING);

			if (!isset($queueArray[$indexId]))
				return;

			$buildItem = $queueArray[$indexId];
			$this->_queue->deleteInQueue($buildItem->id);

			if ($buildItem->time > 0)
			{
				$cost = Building::getBuildingPrice($user, $planet, $buildItem->object_id, true, ($buildItem->operation == $buildItem::OPERATION_DESTROY));

				$planet->metal 		+= $cost['metal'];
				$planet->crystal 	+= $cost['crystal'];
				$planet->deuterium 	+= $cost['deuterium'];

				$planet->update();
			}

			if (count($queueArray) > 1)
			{
				unset($queueArray[$indexId]);

				/** @var Models\Queue[] $queueArray */
				$queueArray = array_values($queueArray);

				foreach ($queueArray as $i => $item)
				{
					if ($buildItem->object_id == $item->object_id && $indexId <= $i)
					{
						$item->level--;
						$item->update();
					}
				}
			}

			$this->_queue->loadQueue();
			$this->_queue->nextBuildingQueue();
		}
	}
}