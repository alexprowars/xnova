<?php

namespace App\Http\Controllers;

use App\Engine\Entity\Building;
use App\Engine\Entity\Ship;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\Resources;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\DefenceObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\Objects\ShipObject;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models;
use App\Models\Fleet;
use Illuminate\Http\Request;
use Throwable;

class InfoController extends Controller
{
	public function index(int $itemId): array
	{
		try {
			$itemObject = ObjectsFactory::get($itemId);
		} catch (Throwable) {
			throw new Exception('Мы не сможем дать вам эту информацию');
		}

		$price = $itemObject->getPrice();

		$result = [
			'id' => $itemId,
			'name' => $itemObject->getName(),
			'code' => $itemObject->getCode(),
			'description' => __('info.description.' . $itemId),
			'production' => null,
			'destroy' => null,
			'fleet' => null,
			'defence' => null,
			'missile' => null,
		];

		if (($itemId >= 1 && $itemId <= 4) || $itemId == 12) {
			$result['production'] = $this->getProductionTable($itemObject);
		} elseif ($itemId >= 22 && $itemId <= 24) {
			$result['production'] = $this->getStorageProduction($itemObject);
		} elseif ($itemId == 42) {
			$result['production'] = $this->getPhalanxRange($itemObject);
		} elseif ($itemId == 34) {
			if ($this->planet->getLevel($itemId) > 0) {
				$list = [];

				$fleets = Models\Fleet::query()
					->where('id', $this->user->id)
					->where('end_galaxy', $this->planet->galaxy)
					->where('end_system', $this->planet->system)
					->where('end_planet', $this->planet->planet)
					->where('end_type', $this->planet->planet_type)
					->where('mess', 3)
					->get();

				foreach ($fleets as $item) {
					$list[] = [
						'id' => $item->id,
						'start_galaxy' => $item->start_galaxy,
						'system' => $item->start_system,
						'planet' => $item->start_planet,
						'name' => $item->user_name,
					];
				}

				$result['alliance'] = [
					'fleets' => $list,
					'cost' => $this->planet->getLevel($itemId) * 10000,
				];
			}
		} elseif ($itemObject instanceof ShipObject) {
			$fleet = [];
			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($itemObject->getWeaponType() == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($itemObject->getWeaponType() == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($itemObject->getWeaponType() == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['attack'] = $itemObject->getAttack();
			$fleet['attack_full'] = round($itemObject->getAttack() * $attTech);
			$fleet['shield'] = $itemObject->getShield();
			$fleet['capacity'] = $itemObject->getCapacity();
			$fleet['speed'] = $itemObject->getSpeed();
			$fleet['speed_full'] = Ship::createEntity($itemId, 1, $this->planet)->getSpeed();
			$fleet['consumption'] = $itemObject->getConsumption();
			$fleet['resources'] = [];

			foreach ($price as $res => $value) {
				$fleet['resources'][$res] = [
					'base' => $value,
					'full' => $value * $this->user->bonus('res_fleet'),
				];
			}

			$fleet['type_engine'] = $itemObject->getEngineType();
			$fleet['type_weapon'] = $itemObject->getWeaponType();
			$fleet['type_armour'] = $itemObject->getArmorType();
			$fleet['rapidfire'] = $this->getRapidfire($itemObject);

			$result['fleet'] = $fleet;
		} elseif ($itemObject instanceof DefenceObject) {
			$fleet = [];
			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));
			$fleet['shield'] = $itemObject->getShield();

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($itemObject->getWeaponType() == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($itemObject->getWeaponType() == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($itemObject->getWeaponType() == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['attack'] = $itemObject->getAttack();
			$fleet['attack_full'] = round($itemObject->getAttack() * $attTech);
			$fleet['resources'] = [];

			foreach ($price as $res => $value) {
				$fleet['resources'][$res] = [
					'base' => $value,
					'full' => $value * $this->user->bonus('res_fleet'),
				];
			}

			$fleet['rapidfire'] = $this->getRapidfire($itemObject);
			$fleet['type_weapon'] = $itemObject->getWeaponType();
			$fleet['type_armour'] = $itemObject->getArmorType();

			$result['defence'] = $fleet;
		}

		if ($itemId <= 44 && $itemId != 33 && $itemId != 41) {
			$entity = $this->planet->getEntityUnit($itemId);

			if ($entity instanceof Building && $entity->getLevel() > 0) {
				$time = ceil($entity->getTime() / 2);

				if ($time < 1) {
					$time = 1;
				}

				$result['destroy'] = [
					'level' => $entity->getLevel(),
					'resources' => $entity->getDestroyPrice(),
					'time' => $time,
				];
			}
		}

		return $result;
	}

	public function missiles(Request $request): void
	{
		$icm = abs((int) $request->post('interceptor', 0));
		$ipm = abs((int) $request->post('interplanetary', 0));

		if ($icm > $this->planet->getLevel('interceptor_misil')) {
			$icm = $this->planet->getLevel('interceptor_misil');
		}
		if ($ipm > $this->planet->getLevel('interplanetary_misil')) {
			$ipm = $this->planet->getLevel('interplanetary_misil');
		}

		$this->planet->updateAmount('interceptor_misil', -$icm, true);
		$this->planet->updateAmount('interplanetary_misil', -$ipm, true);
		$this->planet->update();
	}

	public function alliance(int $itemId, Request $request): void
	{
		$fleetId = (int) $request->post('fleet', 0);

		if ($fleetId <= 0) {
			throw new Exception('Флот отсутствует у планеты');
		}

		$fleet = Fleet::query()
			->where('id', $fleetId)
			->coordinates(FleetDirection::END, $this->planet->coordinates)
			->where('mess', 3)
			->first();

		if (!$fleet) {
			throw new Exception('Флот отсутствует у планеты');
		}

		$tt = 0;

		foreach ($fleet->entities as $entity) {
			$unitObject = ObjectsFactory::get($entity->id);

			if ($unitObject instanceof ShipObject) {
				$tt += $unitObject->getStayConsumption() * $entity->count;
			}
		}

		$max = $this->planet->getLevel($itemId) * 10000;

		if ($max > $this->planet->deuterium) {
			$cur = $this->planet->deuterium;
		} else {
			$cur = $max;
		}

		$times = round(($cur / $tt) * 3600);

		$this->planet->deuterium -= $cur;
		$this->planet->update();

		$fleet->end_stay->addSeconds($times);
		$fleet->end_date->addSeconds($times);
		$fleet->update();
	}

	private function getProductionTable(BaseObject $itemObject): array
	{
		$ActualNeed = $ActualProd = 0;
		$entityUnit = $this->planet->getEntityUnit($itemObject);

		if ($entityProduction = $entityUnit->getProduction()) {
			$Prod[1] = $entityProduction->get(Resources::METAL);
			$Prod[2] = $entityProduction->get(Resources::CRYSTAL);
			$Prod[3] = $entityProduction->get(Resources::DEUTERIUM);
			$Prod[4] = $entityProduction->get(Resources::ENERGY);

			if ($itemObject->getId() != 12) {
				$ActualNeed = floor($Prod[4]);
				$ActualProd = floor($Prod[$itemObject->getId()]);
			} else {
				$ActualNeed = floor($Prod[3]);
				$ActualProd = floor($Prod[4]);
			}
		}

		$startLevel = max(1, $entityUnit->getLevel() - 2);

		$items = [];

		for ($level = $startLevel; $level < $startLevel + 10; $level++) {
			$production = $entityUnit->setLevel($level)
				->getProduction();

			if (!$production) {
				break;
			}

			$row = [
				'level' => $level,
			];

			$Prod[1] = $production->get(Resources::METAL);
			$Prod[2] = $production->get(Resources::CRYSTAL);
			$Prod[3] = $production->get(Resources::DEUTERIUM);
			$Prod[4] = $production->get(Resources::ENERGY);

			if ($itemObject->getId() != 12) {
				$row['prod'] = (int) floor($Prod[$itemObject->getId()]);
				$row['prod_diff'] = (int) floor($Prod[$itemObject->getId()] - $ActualProd);
				$row['need'] = (int) floor($Prod[4]);
				$row['need_diff'] = (int) floor($Prod[4] - $ActualNeed);
			} else {
				$row['prod'] = (int) floor($Prod[4]);
				$row['prod_diff'] = (int) floor($Prod[4] - $ActualProd);
				$row['need'] = (int) floor($Prod[3]);
				$row['need_diff'] = (int) floor($Prod[3] - $ActualNeed);
			}

			$items[] = $row;
		}

		return $items;
	}

	private function getStorageProduction(BaseObject $itemObject): array
	{
		$currentLevel = $this->planet->getLevel($itemObject->getId());
		$startLevel = max(1, $currentLevel - 2);

		$items = [];

		for ($level = $startLevel; $level < $startLevel + 10; $level++) {
			$row = [
				'level' => $level,
			];

			$row['prod'] = floor((config('game.baseStorageSize') + floor(50000 * round(1.6 ** $level))) * $this->user->bonus('storage')) / 1000;
			$row['prod_diff'] = $row['prod'] - floor((config('game.baseStorageSize') + floor(50000 * round(1.6 ** $currentLevel))) * $this->user->bonus('storage')) / 1000;

			$items[] = $row;
		}

		return $items;
	}

	private function getPhalanxRange(BaseObject $itemObject): array
	{
		$currentLevel = $this->planet->getLevel($itemObject->getId());
		$startLevel = max(1, $currentLevel - 2);

		$items = [];

		for ($level = $startLevel; $level < $startLevel + 10; $level++) {
			$items[] = [
				'level' => $level,
				'range' => ($level * $level) - 1,
			];
		}

		return $items;
	}

	private function getRapidfire(ShipObject|DefenceObject $item): array
	{
		$result = ['to' => [], 'from' => []];

		$objects = Vars::getObjectsByType([ItemType::FLEET, ItemType::DEFENSE]);
		$unitRapidfire = $item->getRapidfire();

		/** @var ShipObject|DefenceObject $object */
		foreach ($objects as $object) {
			$rapidfire = $object->getRapidfire();

			if (isset($unitRapidfire[$object->getId()]) && $unitRapidfire[$object->getId()] > 1) {
				$result['to'][$object->getId()] = $unitRapidfire[$object->getId()];
			}

			if (isset($rapidfire[$item->getId()]) && $rapidfire[$item->getId()] > 1) {
				$result['from'][$item->getId()] = $rapidfire[$item->getId()];
			}
		}

		return $result;
	}
}
