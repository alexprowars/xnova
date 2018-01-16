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

		$actualCount = $this->_queue->getCount(Queue::QUEUE_TYPE_BUILDING);

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

				foreach ($this->_queue->get(Queue::QUEUE_TYPE_BUILDING) AS $item)
				{
					if ($item['i'] == $elementId)
						$inArray++;
				}
			}
			else
				$inArray = 0;

			$build = $planet->getBuild($elementId);

			if (!$build)
				return false;

			$ActualLevel = $build['level'];

			if (!$destroy)
			{
				$BuildLevel = $ActualLevel + 1 + $inArray;

				$planet->setBuild($elementId, $build['level'] + $inArray);
				$BuildTime = Building::getBuildingTime($user, $planet, $elementId);
				$planet->setBuild($elementId, $build['level'] - $inArray);
			}
			else
			{
				$BuildLevel = $ActualLevel - 1 + $inArray;

				$planet->setBuild($elementId, $build['level'] - $inArray);
				$BuildTime = Building::getBuildingTime($user, $planet, $elementId) / 2;
				$planet->setBuild($elementId, $build['level'] + $inArray);
			}

			if ($queueID == 1)
				$BuildEndTime = time() + $BuildTime;
			else
			{
				$queueArray = $this->_queue->get(Queue::QUEUE_TYPE_BUILDING);

				$PrevBuild = $queueArray[$actualCount - 1];
				$BuildEndTime = $PrevBuild['e'] + $BuildTime;
			}

			$this->_queue->set(Queue::QUEUE_TYPE_BUILDING, [
				'i' => $elementId,
				'l' => $BuildLevel,
				't' => 0,
				's' => 0,
				'e' => $BuildEndTime,
				'd' => $destroy ? 1 : 0
			]);
		}

		return true;
	}

	public function delete ($elementId)
	{
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();

		if ($this->_queue->getCount(Queue::QUEUE_TYPE_BUILDING))
		{
			$queueArray 	= $this->_queue->get(Queue::QUEUE_TYPE_BUILDING);
			$ActualCount 	= count($queueArray);

			if (!isset($queueArray[$elementId]))
				return;

			$canceledArray = $queueArray[$elementId];

			$queue = $this->_queue->get();

			if ($ActualCount > 1)
			{
				unset($queueArray[$elementId]);

				$queueArray = array_values($queueArray);

				if ($elementId == 0)
					$BuildEndTime = time();
				else
					$BuildEndTime = $queueArray[0]['s'];

				foreach ($queueArray AS $i => &$listArray)
				{
					$listArray['t'] = Building::getBuildingTime($user, $planet, $listArray['i']);

					if ($listArray['d'] == 1)
						$listArray['t'] = ceil($listArray['t'] / 2);

					$BuildEndTime += $listArray['t'];

					$listArray['e'] = $BuildEndTime;

					if ($canceledArray['i'] == $listArray['i'] && $elementId <= $i)
						$listArray['l']--;
				}

				unset($listArray);

				$queue[Queue::QUEUE_TYPE_BUILDING] = $queueArray;
			}
			else
				unset($queue[Queue::QUEUE_TYPE_BUILDING]);

			$this->_queue->set(false, $queue);

			if ($canceledArray['s'] > 0)
			{
				$cost = Building::getBuildingPrice($user, $planet, $canceledArray['i'], true, ($canceledArray['d'] == 1));

				$planet->metal 		+= $cost['metal'];
				$planet->crystal 	+= $cost['crystal'];
				$planet->deuterium 	+= $cost['deuterium'];
			}

			$this->_queue->saveQueue();
		}
	}
}