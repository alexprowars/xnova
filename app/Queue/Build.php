<?php

namespace Xnova\Queue;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Xnova\Building;
use Xnova\Queue;
use Xnova\Models;
use Xnova\Entity;

class Build
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId, $destroy = false)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		$maxBuidSize = Config::get('settings.maxBuildingQueue', 1);

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

			Models\Queue::query()->create([
				'type' => Models\Queue::TYPE_BUILD,
				'operation' => $destroy ? Models\Queue::OPERATION_DESTROY : Models\Queue::OPERATION_BUILD,
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

			if (!$this->_queue->deleteInQueue($buildItem->id))
				$buildItem->delete();

			if ($buildItem->time > 0)
			{
				$entity = new Entity\Building($buildItem->object_id, $buildItem->level, new Entity\Context($user, $planet));

				$cost = $buildItem->operation == $buildItem::OPERATION_DESTROY ? $entity->getDestroyPrice() : $entity->getPrice();

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