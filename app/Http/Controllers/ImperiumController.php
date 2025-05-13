<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Models\Fleet;
use App\Models\Planet;

class ImperiumController extends Controller
{
	public function index()
	{
		$parse = [];

		$build_hangar_full = [];

		$fleetsFly = [];

		$fleets = Fleet::query()
			->whereBelongsTo($this->user)
			->get();

		foreach ($fleets as $fleet) {
			$key = $fleet->splitStartPosition() . ':' . $fleet->start_type->value;

			$fleetsFly[$key] ??= [];

			$fleetData = $fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$fleetsFly[$key][$shipId] ??= 0;
				$fleetsFly[$key][$shipId] += $shipArr['count'];

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
			->whereBelongsTo($this->user);

		$this->user->getPlanetListSortQuery($planets);

		$planets = $planets->get();

		foreach ($planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction()->update(true);

			$row = [];

			$row['id'] = $planet->id;
			$row['image'] = $planet->image;
			$row['name'] = $planet->name;
			$row['position'] = [
				'galaxy' => (int) $planet->galaxy,
				'system' => (int) $planet->system,
				'planet' => (int) $planet->planet,
			];
			$row['fields'] = $planet->field_current;
			$row['fields_max'] = $planet->getMaxFields();

			$row['resources'] = [];
			$row['factor'] = [];

			$row['resources']['energy'] = [
				'value' => $planet->energy_max - abs($planet->energy_used),
				'production' => $planet->energy_max,
			];

			foreach (Vars::getResources() as $res) {
				$row['resources'][$res] = [
					'value' => $planet->{$res},
					'production' => $planet->{$res . '_perhour'},
					'storage' => floor((config('game.baseStorageSize') + floor(50000 * round(1.6 ** $planet->getLevel($res . '_store')))) * $this->user->bonus('storage'))
				];
			}

			foreach (Vars::getItemsByType(ItemType::PRODUCTION) as $ProdID) {
				$row['factor'][$ProdID] = $planet->entities->where('entity_id', $ProdID)->first()?->factor * 10;
			}

			$build_hangar = [];

			$queueManager = new QueueManager($this->user, $planet);
			$queueManager->checkUnitQueue();

			foreach (QueueType::cases() as $type) {
				$queue = $queueManager->get($type);

				if (!count($queue)) {
					continue;
				}

				foreach ($queue as $q) {
					if (!isset($build_hangar[$q->object_id]) || Vars::getItemType($q->object_id) == ItemType::BUILDING) {
						$build_hangar[$q->object_id] = (int) $q->level;
					} else {
						$build_hangar[$q->object_id] += (int) $q->level;
					}

					if (!isset($build_hangar_full[$q->object_id]) || Vars::getItemType($q->object_id) == ItemType::BUILDING) {
						$build_hangar_full[$q->object_id] = (int) $q->level;
					} else {
						$build_hangar_full[$q->object_id] += (int) $q->level;
					}
				}
			}

			$row['elements'] = [];

			foreach (Vars::getItemsByType([ItemType::BUILDING, ItemType::FLEET, ItemType::DEFENSE]) as $id) {
				if (!$planet->getLevel($id)) {
					continue;
				}

				$item = [
					'id' => $id,
					'fly' => 0
				];

				$item['value'] = $planet->getLevel($id);
				$item['build'] = $build_hangar[$id] ?? 0;

				if (Vars::getItemType($id) == ItemType::FLEET) {
					$item['fly'] = $fleetsFly[$planet->galaxy . ':' . $planet->system . ':' . $planet->planet . ':' . $planet->planet_type->value][$id] ?? 0;
				}

				$row['elements'][$id] = $item;
			}

			$parse['planets'][] = $row;
		}

		$parse['tech'] = [];

		foreach (Vars::getItemsByType(ItemType::TECH) as $i) {
			if ($this->user->getTechLevel($i) <= 0) {
				continue;
			}

			$parse['tech'][] = [
				'id' => $i,
				'value' => $this->user->getTechLevel($i),
				'build' => $build_hangar_full[$i] ?? 0,
			];
		}

		return $parse;
	}
}
