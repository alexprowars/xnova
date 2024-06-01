<?php

namespace App\Http\Controllers;

use App\Engine\Entity\Ship;
use App\Engine\Vars;
use App\Exceptions\ErrorException;
use App\Exceptions\SuccessException;
use App\Models;
use Illuminate\Support\Facades\Request;

class InfoController extends Controller
{
	public function index(int $element)
	{
		return $this->showBuildingInfoPage($element);
	}

	private function showRapidFireTo($BuildID)
	{
		$ResultString = '';

		$storage = Vars::getStorage();

		$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

		foreach ($res as $Type) {
			if (isset($storage['CombatCaps'][$BuildID]['sd'][$Type]) && $storage['CombatCaps'][$BuildID]['sd'][$Type] > 1) {
				$ResultString .= __('info.nfo_rf_again') . " <font color=\"#00ff00\">" . $storage['CombatCaps'][$BuildID]['sd'][$Type] . "</font> единиц " . __('main.tech.' . $Type) . "<br>";
			}
		}

		return $ResultString;
	}

	private function showRapidFireFrom($BuildID)
	{
		$ResultString = '';

		$storage = Vars::getStorage();

		$res = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

		foreach ($res as $Type) {
			if (isset($storage['CombatCaps'][$Type]['sd'][$BuildID]) && $storage['CombatCaps'][$Type]['sd'][$BuildID] > 1) {
				$ResultString .= __('main.tech.' . $Type) . " " . __('info.nfo_rf_from') . " <font color=\"#ff0000\">" . $storage['CombatCaps'][$Type]['sd'][$BuildID] . "</font> единиц<br>";
			}
		}

		return $ResultString;
	}

	private function showProductionTable($buildId)
	{
		$CurrentBuildtLvl = $this->planet->getLevel($buildId);

		$ActualNeed = $ActualProd = 0;

		if ($buildId != 42 && !($buildId >= 22 && $buildId <= 24)) {
			$res = $this->planet->getEntity($buildId)->unit()->getProduction();

			$Prod[1] = $res->get('metal');
			$Prod[2] = $res->get('crystal');
			$Prod[3] = $res->get('deuterium');
			$Prod[4] = $res->get('energy');

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
				$entity = $this->planet->getEntity($buildId)->unit();
				$entity->setLevel($BuildLevel);

				$res = $entity->getProduction();

				$Prod[1] = $res->get('metal');
				$Prod[2] = $res->get('crystal');
				$Prod[3] = $res->get('deuterium');
				$Prod[4] = $res->get('energy');

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
				$row['range'] = floor((config('settings.baseStorageSize') + floor(50000 * round(1.6 ** $BuildLevel))) * $this->user->bonus('storage')) / 1000;
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
			throw new ErrorException('Мы не сможем дать вам эту информацию');
		}

		$price = Vars::getItemPrice($itemId);

		$parse['i'] = (int) $itemId;

		$parse['production'] = false;
		$parse['destroy'] = false;
		$parse['fleet'] = false;
		$parse['defence'] = false;
		$parse['missile'] = false;

		$storage = Vars::getStorage();

		if (($itemId >= 1 && $itemId <= 4) || $itemId == 12 || $itemId == 42 || ($itemId >= 22 && $itemId <= 24)) {
			$parse['production'] = $this->showProductionTable($itemId);
		} elseif ($itemId == 34) {
			$parse['msg'] = '';

			if (Request::post('send') && Request::post('fleet')) {
				$fleetId = (int) Request::post('fleet', 0);

				$fleet = Models\Fleet::query()
					->where('id', $fleetId)
					->where('end_galaxy', $this->planet->galaxy)
					->where('end_system', $this->planet->system)
					->where('end_planet', $this->planet->planet)
					->where('end_type', $this->planet->planet_type)
					->where('mess', 3)
					->first();

				if (!$fleet) {
					throw new ErrorException('Флот отсутствует у планеты');
				} else {
					$tt = 0;

					foreach ($fleet->getShips() as $type => $ship) {
						if ($type > 100) {
							$tt += $storage['CombatCaps'][$type]['stay'] * $ship['count'];
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
					$fleet->end_time->addSeconds($times);
					$fleet->update();

					throw new SuccessException('Ракета с дейтерием отправлена на орбиту вашей планете');
				}
			}

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
		} elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_FLEET) {
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($storage['CombatCaps'][$itemId]['type_gun'] == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($storage['CombatCaps'][$itemId]['type_gun'] == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($storage['CombatCaps'][$itemId]['type_gun'] == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['rapidfire'] = [
				'to' => $this->showRapidFireTo($itemId),
				'from' => $this->showRapidFireFrom($itemId)
			];

			$fleet['attack'] = $storage['CombatCaps'][$itemId]['attack'];
			$fleet['attack_full'] = round($storage['CombatCaps'][$itemId]['attack'] * $attTech);
			$fleet['shield'] = $storage['CombatCaps'][$itemId]['shield'];
			$fleet['capacity'] = $storage['CombatCaps'][$itemId]['capacity'];
			$fleet['speed'] = $storage['CombatCaps'][$itemId]['speed'];
			$fleet['speed_full'] = Ship::createEntity($itemId, 1, $this->planet)->getSpeed();
			$fleet['consumption'] = $storage['CombatCaps'][$itemId]['consumption'];

			$fleet['resources'] = [];

			foreach ($price as $res => $value) {
				$fleet['resources'][$res] = [
					'base' => $value,
					'full' => $value * $this->user->bonus('res_fleet')
				];
			}

			$engine = ['', 'Ракетный', 'Импульсный', 'Гиперпространственный'];
			$gun = ['', 'Лазерное', 'Ионное', 'Плазменное'];
			$armour = ['', 'Легкая', 'Средняя', 'Тяжелая', 'Монолитная'];

			$fleet['type_engine'] = $engine[$storage['CombatCaps'][$itemId]['type_engine']];
			$fleet['type_gun'] = $gun[$storage['CombatCaps'][$itemId]['type_gun']];
			$fleet['type_armour'] = $armour[$storage['CombatCaps'][$itemId]['type_armour']];

			$parse['fleet'] = $fleet;
		} elseif (Vars::getItemType($itemId) == Vars::ITEM_TYPE_DEFENSE) {
			$fleet = [];

			$fleet['armor'] = floor(($price['metal'] + $price['crystal']) / 10);
			$fleet['armor_full'] = round($fleet['armor'] * (1 + $this->user->getTechLevel('defence') * 0.05));

			if (isset($storage['CombatCaps'][$itemId]['shield'])) {
				$fleet['shield'] = $storage['CombatCaps'][$itemId]['shield'];
			} else {
				$fleet['shield'] = 0;
			}

			$attTech = 1 + $this->user->getTechLevel('military') * 0.05;

			if ($storage['CombatCaps'][$itemId]['type_gun'] == 1) {
				$attTech += $this->user->getTechLevel('laser') * 0.05;
			} elseif ($storage['CombatCaps'][$itemId]['type_gun'] == 2) {
				$attTech += $this->user->getTechLevel('ionic') * 0.05;
			} elseif ($storage['CombatCaps'][$itemId]['type_gun'] == 3) {
				$attTech += $this->user->getTechLevel('buster') * 0.05;
			}

			$fleet['attack'] = $storage['CombatCaps'][$itemId]['attack'];
			$fleet['attack_full'] = round($storage['CombatCaps'][$itemId]['attack'] * $attTech);

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

				$fleet['type_gun'] = $gun[$storage['CombatCaps'][$itemId]['type_gun']];
				$fleet['type_armour'] = $armour[$storage['CombatCaps'][$itemId]['type_armour']];
				$fleet['rapidfire'] = [];

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $Type) {
					if (!isset($storage['CombatCaps'][$Type])) {
						continue;
					}

					$enemyPrice = Vars::getItemPrice($Type);

					$enemy_durability = ($enemyPrice['metal'] + $enemyPrice['crystal']) / 10;

					$rapid = $storage['CombatCaps'][$itemId]['attack'] * ($storage['CombatCaps'][$itemId]['amplify'][$Type] ?? 1) / $enemy_durability;

					if ($rapid >= 1) {
						$fleet['rapidfire'][$Type]['TO'] = floor($rapid);
					}

					$rapid = $storage['CombatCaps'][$Type]['attack'] * ($storage['CombatCaps'][$Type]['amplify'][$itemId] ?? 1) / $fleet['armor'];

					if ($rapid >= 1) {
						$fleet['rapidfire'][$Type]['FROM'] = floor($rapid);
					}
				}
			}

			$parse['defence'] = $fleet;

			if ($itemId >= 500 && $itemId < 600) {
				if (Request::post('missiles')) {
					$icm = abs((int) Request::post('interceptor', 0));
					$ipm = abs((int) Request::post('interplanetary', 0));

					if ($icm > $this->planet->getLevel('interceptor_misil')) {
						$icm = $this->planet->getLevel('interceptor_misil');
					}
					if ($ipm > $this->planet->getLevel('interplanetary_misil')) {
						$ipm = $this->planet->getLevel('interplanetary_misil');
					}

					$this->planet->updateAmount('interceptor_misil', -$icm, true);
					$this->planet->updateAmount('interplanetary_misil', -$ipm, true);
					$this->planet->update();

					throw new SuccessException('Ракеты уничтожены');
				}

				$parse['missile'] = [
					'interceptor' => $this->planet->getLevel('interceptor_misil'),
					'interplanetary' => $this->planet->getLevel('interplanetary_misil')
				];
			}
		}

		if ($itemId <= 44 && $itemId != 33 && $itemId != 41 && !($itemId >= 601 && $itemId <= 615) && !($itemId >= 502 && $itemId <= 503)) {
			$entity = $this->planet->getEntity($itemId)->unit();

			if ($entity?->getLevel() > 0) {
				$time = ceil($entity->getTime() / 2);

				if ($time < 1) {
					$time = 1;
				}

				$parse['destroy'] = [
					'level' => $entity->getLevel(),
					'resources' => $entity->getDestroyPrice(),
					'time' => $time
				];
			}
		}

		return response()->state($parse);
	}
}
