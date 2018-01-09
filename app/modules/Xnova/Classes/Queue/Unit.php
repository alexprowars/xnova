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

class Unit
{
	private $_queue = null;

	public function __construct (Queue $queue)
	{
		$this->_queue = $queue;
	}

	public function add ($elementId, $count)
	{
		$registry = Di::getDefault()->getShared('registry');
		
		$planet = $this->_queue->getPlanet();
		$user = $this->_queue->getUser();
		
		if (!Building::IsTechnologieAccessible($user, $planet, $elementId))
			return;

		$BuildArray = $this->_queue->get(Queue::QUEUE_TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503)
		{
			$Missiles = [];
			$Missiles[502] = $planet->{$registry->resource[502]};
			$Missiles[503] = $planet->{$registry->resource[503]};

			$MaxMissiles = $planet->getBuildLevel('missile_facility') * 10;

			foreach ($BuildArray AS $item)
			{
				if (($item['i'] == 502 || $item['i'] == 503) && $item['l'] != 0)
					$Missiles[$item['i']] += $item['l'];
			}
		}

		if (isset($registry->pricelist[$elementId]['max']))
		{
			$total = $planet->{$registry->resource[$elementId]};

			if (isset($BuildArray[$elementId]))
				$total += $BuildArray[$elementId];

			$count = min($count, max(($registry->pricelist[$elementId]['max'] - $total), 0));
		}

		if (($elementId == 502 || $elementId == 503) && isset($Missiles) && isset($MaxMissiles))
		{
			$ActuMissiles 	= $Missiles[502] + (2 * $Missiles[503]);
			$MissilesSpace 	= $MaxMissiles - $ActuMissiles;

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

		$count = min($count, Building::GetMaxConstructibleElements($elementId, $planet, $user));

		if ($count > 0)
		{
			$Ressource = Building::GetElementRessources($elementId, $count, $user);

			$planet->metal 		-= $Ressource['metal'];
			$planet->crystal 	-= $Ressource['crystal'];
			$planet->deuterium 	-= $Ressource['deuterium'];

			$this->_queue->set(Queue::QUEUE_TYPE_SHIPYARD, [
				'i' => $elementId,
				'l' => $count,
				't' => 0,
				's' => 0,
				'e' => 0,
				'd' => 0
			]);

			$this->_queue->saveQueue();
		}
	}
}