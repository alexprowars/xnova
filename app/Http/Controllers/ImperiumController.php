<?php

namespace App\Http\Controllers;

use App\Models\Fleet;
use App\Models\Planet;
use App\Queue;
use App\Controller;
use App\Vars;

class ImperiumController extends Controller
{
	public function index()
	{
		$parse = [];

		$build_hangar_full = [];

		$fleet_fly = [];

		$fleets = Fleet::query()
			->where('user_id', $this->user->id)
			->get();

		foreach ($fleets as $fleet) {
			if (!isset($fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type])) {
				$fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type] = [];
			}

			$fleetData = $fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				if (!isset($fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type][$shipId])) {
					$fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type][$shipId] = 0;
				}

				$fleet_fly[$fleet->splitStartPosition() . ':' . $fleet->start_type][$shipId] += $shipArr['count'];

				if ($fleet->target_user_id == $this->user->id) {
					if (!isset($build_hangar_full[$shipId])) {
						$build_hangar_full[$shipId] = 0;
					}

					$build_hangar_full[$shipId] += $shipArr['count'];
				}
			}
		}

		$parse['planets'] = [];

		$planets = Planet::query()
			->where('user_id', $this->user->id);

		$this->user->getPlanetListSortQuery($planets);

		$planets = $planets->get();

		foreach ($planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction()->update(true);

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
			$row['fields_max'] = $planet->getMaxFields();

			$row['resources'] = [];
			$row['factor'] = [];

			$row['resources']['energy'] = [
				'current' => $planet->energy_max - abs($planet->energy_used),
				'production' => $planet->energy_max,
				'storage' => $planet->getLevel('solar_plant') ? round($planet->energy_ak / (250 * $planet->getLevel('solar_plant')) * 100) : 0
			];

			foreach (Vars::getResources() as $res) {
				$row['resources'][$res] = [
					'current' => $planet->{$res},
					'production' => $planet->{$res . '_perhour'},
					'storage' => floor((config('settings.baseStorageSize') + floor(50000 * round(pow(1.6, $planet->getLevel($res . '_store'))))) * $this->user->bonusValue('storage'))
				];
			}

			foreach (Vars::getItemsByType('prod') as $ProdID) {
				$row['factor'][$ProdID] = $planet->getEntity($ProdID)?->factor * 10;
			}

			$build_hangar = [];

			$queueManager = new Queue($this->user, $planet);
			$queueManager->checkUnitQueue();

			foreach ($queueManager->getTypes() as $type) {
				$queue = $queueManager->get($type);

				if (!count($queue)) {
					continue;
				}

				foreach ($queue as $q) {
					if (!isset($build_hangar[$q->object_id]) || Vars::getItemType($q->object_id) == Vars::ITEM_TYPE_BUILING) {
						$build_hangar[$q->object_id] = (int) $q->level;
					} else {
						$build_hangar[$q->object_id] += (int) $q->level;
					}

					if (!isset($build_hangar_full[$q->object_id]) || Vars::getItemType($q->object_id) == Vars::ITEM_TYPE_BUILING) {
						$build_hangar_full[$q->object_id] = (int) $q->level;
					} else {
						$build_hangar_full[$q->object_id] += (int) $q->level;
					}
				}
			}

			$row['elements'] = [];

			foreach (Vars::getItemsByType([Vars::ITEM_TYPE_BUILING, Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]) as $id) {
				if (!$planet->getLevel($id)) {
					continue;
				}

				$item = [
					'id' => $id,
					'fly' => 0
				];

				$item['current'] = $planet->getLevel($id);
				$item['build'] = $build_hangar[$id] ?? 0;

				if (Vars::getItemType($id) == Vars::ITEM_TYPE_FLEET) {
					$item['fly'] = $fleet_fly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type][$id] ?? 0;
				}

				$row['elements'][$id] = $item;
			}

			$parse['planets'][] = $row;
		}

		$parse['credits'] = (int) $this->user->credits;

		$parse['tech'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_TECH) as $i) {
			if ($this->user->getTechLevel($i) <= 0) {
				continue;
			}

			$parse['tech'][$i] = [
				'current' => $this->user->getTechLevel($i),
				'build' => $build_hangar_full[$i] ?? 0,
			];
		}

		return $parse;
	}
}
