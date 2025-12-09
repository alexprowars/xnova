<?php

namespace App\Http\Controllers;

use App\Engine\Entity\Building;
use App\Engine\Entity\Ship;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\Resources;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models;
use App\Models\Fleet;
use Illuminate\Http\Request;

class InfoController extends Controller
{
	public function index(int $element)
	{
		return $this->showBuildingInfoPage($element);
	}

	private function showRapidFireTo($BuildID)
	{
		$result = '';

		$res = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($res as $Type) {
			$unitData = Vars::getUnitData($BuildID);

			if (isset($unitData['sd'][$Type]) && $unitData['sd'][$Type] > 1) {
				$result .= __('info.nfo_rf_again') . ' <span style="color: #00ff00">' . $unitData['sd'][$Type] . '</span> единиц ' . __('main.tech.' . $Type) . '<br>';
			}
		}

		return $result;
	}

	private function showRapidFireFrom($BuildID)
	{
		$result = '';

		$res = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($res as $Type) {
			$unitData = Vars::getUnitData($Type);

			if (isset($unitData['sd'][$BuildID]) && $unitData['sd'][$BuildID] > 1) {
				$result .= __('main.tech.' . $Type) . ' ' . __('info.nfo_rf_from') . ' <span style="color: #ff0000">' . $unitData['sd'][$BuildID] . '</span> единиц<br>';
			}
		}

		return $result;
	}

	private function showProductionTable($buildId)
	{
		$CurrentBuildtLvl = $this->planet->getLevel($buildId);

		$ActualNeed = $ActualProd = 0;

		if ($buildId != 42 && !($buildId >= 22 && $buildId <= 24)) {
			$res = $this->planet->getEntityUnit($buildId)->getProduction();

			$Prod[1] = $res->get(Resources::METAL);
			$Prod[2] = $res->get(Resources::CRYSTAL);
			$Prod[3] = $res->get(Resources::DEUTERIUM);
			$Prod[4] = $res->get(Resources::ENERGY);

			if ($buildId != 12) {
				$ActualNeed = floor($Prod[4]);
				$ActualProd = floor($Prod[$buildId]);
			} else {
				$ActualNeed = floor($Prod[3]);
				$ActualProd = floor($Prod[4]);
			}
		}

		$BuildStartLvl = $CurrentBuildtLvl - 2;

		if ($BuildStartLvl < 1) {
			$BuildStartLvl = 1;
		}

		$items = [];

		$ProdFirst = 0;

		for ($BuildLevel = $BuildStartLvl; $BuildLevel < $BuildStartLvl + 10; $BuildLevel++) {
			$row = [];

			if ($buildId != 42 && !($buildId >= 22 && $buildId <= 24)) {
				$entity = $this->planet->getEntityUnit($buildId);
				$entity->setLevel($BuildLevel);

				$res = $entity->getProduction();

				$Prod[1] = $res->get(Resources::METAL);
				$Prod[2] = $res->get(Resources::CRYSTAL);
				$Prod[3] = $res->get(Resources::DEUTERIUM);
				$Prod[4] = $res->get(Resources::ENERGY);

				if ($buildId != 12) {
					$row['prod'] = floor($Prod[$buildId]);
					$row['prod_diff'] = floor($Prod[$buildId] - $ActualProd);
					$row['need'] = floor($Prod[4]);
					$row['need_diff'] = floor($Prod[4] - $ActualNeed);
				} else {
					$row['prod'] = floor($Prod[4]);
					$row['prod_diff'] = floor($Prod[4] - $ActualProd);
					$row['need'] = floor($Prod[3]);
					$row['need_diff'] = floor($Prod[3] - $ActualNeed);
				}

				if ($ProdFirst == 0) {
					if ($buildId != 12) {
						$ProdFirst = floor($Prod[$buildId]);
					} else {
						$ProdFirst = floor($Prod[4]);
					}
				}
			} elseif ($buildId >= 22 && $buildId <= 24) {
				$row['range'] = floor((config('game.baseStorageSize') + floor(50000 * round(1.6 ** $BuildLevel))) * $this->user->bonus('storage')) / 1000;
			} else {
				$row['range'] = ($BuildLevel * $BuildLevel) - 1;
			}

			$row['current'] = $CurrentBuildtLvl == $BuildLevel;
			$row['level'] = $BuildLevel;

			$items[] = $row;
		}

		return $items;
	}

	private function showBuildingInfoPage($itemId)
	{
		$parse = [];

		if (!__('main.tech.' . $itemId)) {
			throw new Exception('Мы не сможем дать вам эту информацию');
		}

		$price = Vars::getItemPrice($itemId);

		$parse['i'] = (int) $itemId;

		$parse['production'] = false;
		$parse['destroy'] = false;
		$parse['fleet'] = false;
		$parse['defence'] = false;
		$parse['missile'] = false;

		if (($itemId >= 1 && $itemId <= 4) || $itemId == 12 || $itemId == 42 || ($itemId >= 22 && $itemId <= 24)) {
			$parse['production'] = $this->showProductionTable($itemId);
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

				$parse['alliance'] = [
					'fleets' => $list,
					'cost' => $this->planet->getLevel($itemId) * 10000,
				];
			}
		} elseif (Vars::getItemType($itemId) == ItemType::FLEET) {
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			$unitData = Vars::getUnitData($itemId);

			if ($unitData['type_gun'] == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($unitData['type_gun'] == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($unitData['type_gun'] == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['rapidfire'] = [
				'to' => $this->showRapidFireTo($itemId),
				'from' => $this->showRapidFireFrom($itemId),
			];

			$fleet['attack'] = $unitData['attack'];
			$fleet['attack_full'] = round($unitData['attack'] * $attTech);
			$fleet['shield'] = $unitData['shield'];
			$fleet['capacity'] = $unitData['capacity'];
			$fleet['speed'] = $unitData['speed'];
			$fleet['speed_full'] = Ship::createEntity($itemId, 1, $this->planet)->getSpeed();
			$fleet['consumption'] = $unitData['consumption'];

			$fleet['resources'] = [];

			foreach ($price as $res => $value) {
				$fleet['resources'][$res] = [
					'base' => $value,
					'full' => $value * $this->user->bonus('res_fleet'),
				];
			}

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$fleet['type_engine'] = $engine[$unitData['type_engine']];
			$fleet['type_gun'] = $gun[$unitData['type_gun']];
			$fleet['type_armour'] = $armour[$unitData['type_armour']];

			$parse['fleet'] = $fleet;
		} elseif (Vars::getItemType($itemId) == ItemType::DEFENSE) {
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			$unitData = Vars::getUnitData($itemId);

			if (isset($unitData['shield'])) {
				$fleet['shield'] = $unitData['shield'];
			} else {
				$fleet['shield'] = 0;
			}

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($unitData['type_gun'] == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($unitData['type_gun'] == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($unitData['type_gun'] == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['attack'] = $unitData['attack'];
			$fleet['attack_full'] = round($unitData['attack'] * $attTech);

			$fleet['resources'] = [];

			foreach ($price as $res => $value) {
				$fleet['resources'][$res] = $value;
			}

			$fleet['type_gun'] = false;
			$fleet['type_armour'] = false;
			$fleet['rapidfire'] = false;

			if ($itemId >= 400 && $itemId < 500) {
				$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
				$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

				$fleet['type_gun'] = $gun[$unitData['type_gun']];
				$fleet['type_armour'] = $armour[$unitData['type_armour']];
				$fleet['rapidfire'] = [];

				foreach (Vars::getItemsByType(ItemType::FLEET) as $Type) {
					$rfUnitData = Vars::getUnitData($Type);

					if (!$rfUnitData) {
						continue;
					}

					$enemyPrice = Vars::getItemPrice($Type);

					$enemy_durability = ($enemyPrice['metal'] + $enemyPrice['crystal']) / 10;

					$rapid = $unitData['attack'] * ($unitData['amplify'][$Type] ?? 1) / $enemy_durability;

					if ($rapid >= 1) {
						$fleet['rapidfire'][$Type]['TO'] = floor($rapid);
					}

					$rapid = $rfUnitData['attack'] * ($rfUnitData['amplify'][$itemId] ?? 1) / $fleet['armor'];

					if ($rapid >= 1) {
						$fleet['rapidfire'][$Type]['FROM'] = floor($rapid);
					}
				}
			}

			$parse['defence'] = $fleet;
		}

		if ($itemId <= 44 && $itemId != 33 && $itemId != 41 && !($itemId >= 601 && $itemId <= 615) && !($itemId >= 502 && $itemId <= 503)) {
			$entity = $this->planet->getEntityUnit($itemId);

			if ($entity instanceof Building && $entity->getLevel() > 0) {
				$time = ceil($entity->getTime() / 2);

				if ($time < 1) {
					$time = 1;
				}

				$parse['destroy'] = [
					'level' => $entity->getLevel(),
					'resources' => $entity->getDestroyPrice(),
					'time' => $time,
				];
			}
		}

		return $parse;
	}

	public function missiles(Request $request)
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

	public function alliance($itemId, Request $request)
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
			if ($entity->id > 100) {
				$unitData = Vars::getUnitData($entity->id);

				$tt += $unitData['stay'] * $entity->count;
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
}
