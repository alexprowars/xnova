<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Format;
use Friday\Core\Lang;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Queue;
use Xnova\Controller;
use Xnova\Request;
use Xnova\User;
use Xnova\Vars;

/**
 * @RoutePrefix("/imperium")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ImperiumController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('imperium', 'xnova');

		$this->user->loadPlanet();
	}

	public function indexAction()
	{
		$parse = [];

		$build_hangar_full = [];

		$fleet_fly = [];

		$fleets = Fleet::find(['owner = ?0', 'bind' => [$this->user->getId()]]);

		foreach ($fleets as $fleet)
		{
			if (!isset($fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type]))
				$fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type] = [];

			if (!isset($fleet_fly[$fleet->splitTargetPosition() . ':' . $fleet->end_type]))
				$fleet_fly[$fleet->splitTargetPosition() . ':' . $fleet->end_type] = [];

			$fleetData = $fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				if (!isset($fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId]))
					$fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId] = 0;

				if (!isset($fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId]))
					$fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId] = 0;

				$fleet_fly[$fleet->splitStartPosition().':'.$fleet->start_type][$shipId] -= $shipArr['count'];

				if ($fleet->target_owner == $this->user->id)
					$fleet_fly[$fleet->splitTargetPosition().':'.$fleet->end_type][$shipId] += $shipArr['count'];

				if ($fleet->target_owner == $this->user->id)
				{
					if (!isset($build_hangar_full[$shipId]))
						$build_hangar_full[$shipId] = 0;

					$build_hangar_full[$shipId] += $shipArr['count'];
				}
			}
		}

		$planets = Planet::find([
			'id_owner = :user:',
			'bind' => [
				'user' => $this->user->getId()
			],
			'order' => User::getPlanetListSortQuery($this->user->getUserOption('planet_sort'), $this->user->getUserOption('planet_sort_order'))
		]);

		$parse['planets'] = [];

		foreach ($planets AS $planet)
		{
			$planet->assignUser($this->user);
			$planet->resourceUpdate(time(), true);

			$planet->field_max = $planet->getMaxFields();

			$row = [];

			$row['id'] = (int) $planet->id;
			$row['image'] = $planet->image;
			$row['name'] = $planet->name;
			$row['position'] = [
				'galaxy' => (int) $planet->galaxy,
				'system' => (int) $planet->system,
				'planet' => (int) $planet->planet,
			];
			$row['fields'] = (int) $planet->field_current;
			$row['fields_max'] = (int) $planet->field_max;

			$row['resources'] = [];
			$row['factor'] = [];

			$row['resources']['energy'] = [
				'current' => $planet->energy_max - abs($planet->energy_used),
				'production' => $planet->energy_max,
				'storage' => $planet->getBuildLevel('solar_plant') ? round($planet->energy_ak / (250 * $planet->getBuildLevel('solar_plant')) * 100) : 0
			];

			foreach (Vars::getResources() as $res)
			{
				$row['resources'][$res] = [
					'current' => $planet->{$res},
					'production' => $planet->{$res.'_perhour'},
					'storage' => floor(($this->config->game->baseStorageSize + floor(50000 * round(pow(1.6, $planet->getBuildLevel($res.'_store'))))) * $this->user->bonusValue('storage'))
				];
			}

			foreach ($this->registry->reslist['prod'] as $ProdID)
				$row['factor'][$ProdID] = $planet->getBuild($ProdID)['power'] * 10;

			$build_hangar = [];

			$queueManager = new Queue($this->user, $planet);
			$queueManager->checkUnitQueue();

			foreach ($queueManager->getTypes() AS $type)
			{
				$queue = $queueManager->get($type);

				if (!count($queue))
					continue;

				foreach ($queue AS $q)
				{
					if (!isset($build_hangar[$q->object_id]) || Vars::getItemType($q->object_id) == Vars::ITEM_TYPE_BUILING)
						$build_hangar[$q->object_id] = (int) $q->level;
					else
						$build_hangar[$q->object_id] += (int) $q->level;

					if (!isset($build_hangar_full[$q->object_id]) || Vars::getItemType($q->object_id) == Vars::ITEM_TYPE_BUILING)
						$build_hangar_full[$q->object_id] = (int) $q->level;
					else
						$build_hangar_full[$q->object_id] += (int) $q->level;
				}
			}

			$items = [];

			foreach ($this->registry->resource as $i => $res)
			{
				if (!isset($items[$i]))
				{
					$items[$i] = [
						'current' => 0,
						'build' => 0,
						'fly' => 0
					];
				}

				if (Vars::getItemType($i) == Vars::ITEM_TYPE_BUILING)
				{
					$items[$i]['current'] = $planet->getBuildLevel($i);
					$items[$i]['build'] = $build_hangar[$i] ?? 0;
				}
				elseif (Vars::getItemType($i) == Vars::ITEM_TYPE_FLEET)
				{
					$items[$i]['current'] = $planet->getUnitCount($i);
					$items[$i]['build'] = $build_hangar[$i] ?? 0;
					$items[$i]['fly'] = $fleet_fly[$planet->galaxy.':'.$planet->system.':'.$planet->planet.':'.$planet->planet_type][$i] ?? 0;
				}
				elseif (Vars::getItemType($i) == Vars::ITEM_TYPE_DEFENSE)
				{
					$items[$i]['current'] = $planet->getUnitCount($i);
					$items[$i]['build'] = $build_hangar[$i] ?? 0;
				}
			}

			$row['elements'] = [];

			foreach (Vars::getItemsByType(Vars::ITEM_TYPE_BUILING) as $i)
				$row['elements'][$i] = $items[$i];

			foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $i)
				$row['elements'][$i] = $items[$i];

			foreach (Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE) as $i)
				$row['elements'][$i] = $items[$i];

			$parse['planets'][] = $row;
		}

		$parse['credits'] = (int) $this->user->credits;

		$parse['tech'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_TECH) as $i)
		{
			if ($this->user->getTechLevel($i) <= 0)
				continue;

			$parse['tech'][$i] = [
				'current' => $this->user->getTechLevel($i),
				'build' => $build_hangar_full[$i] ?? 0,
			];
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Империя');
		$this->showTopPanel(false);
	}
}