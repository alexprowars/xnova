<?php

namespace Xnova;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Xnova\User as UsersModel;
use Xnova\Planet\Build;
use Xnova\Planet\Unit;

class Planet extends Models\Planet
{
	use Build;
	use Unit;

	/** @var User */
	private $user;
	public $ally = [];

	public $planet_updated;
	public $metal_perhour = 0;
	public $crystal_perhour = 0;
	public $deuterium_perhour = 0;
	public $spaceLabs;
	public $merchand;
	public $metal_max;
	public $crystal_max;
	public $deuterium_max;
	public $battery_max;
	public $energy_used;
	public $energy_max = 0;
	public $production_level;
	public $metal_production;
	public $metal_base;
	public $crystal_production;
	public $crystal_base;
	public $deuterium_production;
	public $deuterium_base;

	public function assignUser(UsersModel $user)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function checkOwnerPlanet()
	{
		if ($this->id_owner != $this->user->id && $this->id_ally > 0 && ($this->id_ally != $this->user->ally_id || !$this->user->ally['rights']['planet'])) {
			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			$data = $this->find($this->user->planet->id);

			if ($data) {
				$this->fill($data->toArray());
			}

			return false;
		}

		return true;
	}

	public function checkUsedFields()
	{
		$this->getBuildingsData();

		$cnt = 0;

		foreach (Vars::getAllowedBuilds($this->planet_type) as $type) {
			if (isset($this->buildings[$type])) {
				$cnt += $this->buildings[$type]['level'];
			}
		}

		if ($this->field_current != $cnt) {
			$this->field_current = $cnt;
			$this->update();
		}
	}

	public function getMaxFields()
	{
		$fields = (int) $this->field_max;

		$fields += $this->getBuildLevel('terraformer') * 5;
		$fields += config('settings.fieldsByMoonBase', 0) * $this->getBuildLevel('moonbase');

		return $fields;
	}

	public function resourceUpdate($updateTime = 0, $simulation = false)
	{
		if (!$this->user instanceof UsersModel) {
			return false;
		}

		if (!$updateTime) {
			$updateTime = time();
		}

		if ($updateTime < $this->last_update) {
			return false;
		}

		$this->getBuildingsData();

		$this->planet_updated = true;

		foreach (Vars::getResources() as $res) {
			$this->{$res . '_max'}  = floor((config('settings.baseStorageSize', 0) + floor(50000 * round(pow(1.6, $this->getBuildLevel($res . '_store'))))) * $this->user->bonusValue('storage'));
		}

		$this->battery_max = floor(250 * $this->getBuildLevel('solar_plant'));

		$this->resourceProductions();

		$productionTime = $updateTime - $this->last_update;
		$this->last_update = $updateTime;

		if (!defined('CRON')) {
			$this->last_active = $this->last_update;
		}

		if ($this->energy_max == 0) {
			foreach (Vars::getResources() as $res) {
				$this->{$res . '_perhour'} = config('settings.' . $res . '_basic_income', 0);
			}

			$this->production_level = 0;
		} elseif ($this->energy_max >= abs($this->energy_used)) {
			$this->production_level = 100;

			$energy = round(($this->energy_max - abs($this->energy_used)) * ($productionTime / 3600), 2);

			if ($this->battery_max > ($this->energy_ak + $energy)) {
				$this->energy_ak += $energy;
			} else {
				$this->energy_ak = $this->battery_max;
			}
		} else {
			if ($this->energy_ak > 0) {
				$energy = ((abs($this->energy_used) - $this->energy_max) / 3600) * $productionTime;

				if ($this->energy_ak > $energy) {
					$this->production_level = 100;
					$this->energy_ak -= round($energy, 2);
				} else {
					$this->production_level = round((($this->energy_max + $this->energy_ak * 3600) / abs($this->energy_used)) * 100, 1);
					$this->energy_ak = 0;
				}
			} else {
				$this->production_level = round(($this->energy_max / abs($this->energy_used)) * 100, 1);
			}
		}

		$this->production_level = min(max($this->production_level, 0), 100);

		foreach (Vars::getResources() as $res) {
			$this->{$res . '_production'} = 0;

			if ($this->{$res} <= $this->{$res . '_max'}) {
				$this->{$res . '_production'} = ($productionTime * ($this->{$res . '_perhour'} / 3600)) * (0.01 * $this->production_level);

				if (!$this->user->isVacation()) {
					$this->{$res . '_base'} = ($productionTime * (config('settings.' . $res . '_basic_income', 0) / 3600)) * config('settings.resource_multiplier', 1);
				} else {
					$this->{$res . '_base'} = 0;
				}

				$this->{$res . '_production'} = $this->{$res . '_production'} + $this->{$res . '_base'};

				if (($this->{$res} + $this->{$res . '_production'}) > $this->{$res . '_max'}) {
					$this->{$res . '_production'} = $this->{$res . '_max'} - $this->{$res};
				}
			}

			$this->{$res . '_perhour'} = round(floatval($this->{$res . '_perhour'}) * (0.01 * $this->production_level));
			$this->{$res} += $this->{$res . '_production'};

			if ($this->{$res} < 0) {
				$this->{$res} = 0;
			}
		}

		if (!$simulation) {
			$this->update();
		}

		return true;
	}

	public function resourceProductions()
	{
		$this->energy_used 	= 0;
		$this->energy_max 	= 0;

		foreach (Vars::getResources() as $res) {
			$this->{$res . '_perhour'} = 0;
		}

		if ($this->user->isVacation()) {
			return;
		}

		if (in_array($this->planet_type, [3, 5])) {
			foreach (Vars::getResources() as $res) {
				config(['settings.' . $res . '_basic_income' => 0]);
			}

			return;
		}

		$itemsId = Vars::getItemsByType('prod');

		foreach ($itemsId as $ProdID) {
			$type = Vars::getItemType($ProdID);

			if ($type == Vars::ITEM_TYPE_BUILING && $this->getBuildLevel($ProdID) <= 0) {
				continue;
			} elseif ($type == Vars::ITEM_TYPE_FLEET && $this->getUnitCount($ProdID) <= 0) {
				continue;
			}

			if (!Vars::getBuildProduction($ProdID)) {
				continue;
			}

			$BuildLevelFactor = $BuildLevel = 0;

			if ($type == Vars::ITEM_TYPE_BUILING) {
				$build = $this->getBuild($ProdID);

				$BuildLevel = $build['level'];
				$BuildLevelFactor = $build['power'];
			} elseif ($type == Vars::ITEM_TYPE_FLEET) {
				$unit = $this->getUnit($ProdID);

				$BuildLevel = $unit['amount'];
				$BuildLevelFactor = $unit['power'];
			}

			if ($ProdID == 12 && $this->deuterium < 100) {
				$BuildLevelFactor = 0;
			}

			$result = $this->getResourceProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

			foreach (Vars::getResources() as $res) {
				$this->{$res . '_perhour'} += $result[$res];
			}

			if ($ProdID < 4) {
				$this->energy_used += $result['energy'];
			} else {
				$this->energy_max += $result['energy'];
			}
		}
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function getResourceProductionLevel($Element, $BuildLevel, $BuildLevelFactor = 10)
	{
		if ($BuildLevelFactor > 10) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$BuildLevelFactor = 10;
		}

		$return = ['energy' => 0];

		foreach (Vars::getResources() as $res) {
			$return[$res] = 0;
		}

		$return['energy'] = 0;

		$production = Vars::getBuildProduction($Element);

		if (!$production) {
			return $return;
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$energyTech = $this->user->getTechLevel('energy');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildTemp = $this->temp_max;

		foreach (Vars::getResources() as $res) {
			if (isset($production[$res])) {
				$return[$res] = floor(eval($production[$res]) * config('settings.resource_multiplier') * $this->user->bonusValue($res));
			}
		}

		if (isset($production['energy'])) {
			$energy = floor(eval($production['energy']));

			if ($Element < 4) {
				$return['energy'] = $energy;
			} elseif ($Element == 4 || $Element == 12) {
				$return['energy'] = floor($energy * $this->user->bonusValue('energy'));
			} elseif ($Element == 212) {
				$return['energy'] = floor($energy * $this->user->bonusValue('solar'));
			}
		}

		return $return;
	}

	public function getNetworkLevel()
	{
		$list = [$this->getBuildLevel('laboratory')];

		if ($this->user->getTechLevel('intergalactic') > 0) {
			$items = DB::select(
				'SELECT b.id, b.level FROM planets_buildings b
				LEFT JOIN planets p ON p.id = b.planet_id
					WHERE
				b.build_id = :build AND p.id_owner = :user AND b.planet_id != :planet AND b.level > 0 AND p.destruyed = 0 AND p.planet_type = 1
					ORDER BY
				b.level DESC
					LIMIT :level',
				[
					'build' => 31,
					'user' => $this->user->id,
					'planet' => $this->id,
					'level' => $this->user->getTechLevel('intergalactic')
				]
			);

			foreach ($items as $item) {
				$list[] = (int) $item->level;
			}
		}

		return $list;
	}

	public function isAvailableJumpGate()
	{
		return ($this->planet_type == 3 || $this->planet_type == 5) && $this->getBuildLevel('jumpgate') > 0;
	}

	public function getNextJumpTime()
	{
		$jumpGate = $this->getBuild('jumpgate');

		if ($jumpGate && $jumpGate['level'] > 0) {
			$waitTime = (60 * 60) * (1 / $jumpGate['level']);
			$nextJumpTime = $this->last_jump_time + $waitTime;

			if ($nextJumpTime >= time()) {
				return $nextJumpTime - time();
			}
		}

		return 0;
	}

	public function getTopPanelRosources(): array
	{
		$user = Auth::user();

		$data = [];

		foreach (Vars::getResources() as $res) {
			$data[$res] = [
				'current' => floor(floatval($this->{$res})),
				'max' => $this->{$res . '_max'},
				'production' => 0,
				'power' => $this->getBuild($res . '_mine')['power'] * 10
			];

			if (!$user->isVacation()) {
				$data[$res]['production'] = $this->{$res . '_perhour'} + floor(config('settings.' . $res . '_basic_income', 0) * config('settings.resource_multiplier', 1));
			}
		}

		$data['energy'] = [
			'current' => $this->energy_max + $this->energy_used,
			'max' => $this->energy_max
		];

		$data['battery'] = [
			'current' => round($this->energy_ak),
			'max' => $this->battery_max,
			'power' => 0,
			'tooltip' => ''
		];

		$data['credits'] = (int) $user->credits;

		$data['officiers'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $officier) {
			$data['officiers'][$officier] = (int) $user->{Vars::getName($officier)};
		}

		$data['battery']['power'] = ($this->battery_max > 0 ? round($this->energy_ak / $this->battery_max, 2) * 100 : 0);
		$data['battery']['power'] = min(100, max(0, $data['battery']['power']));

		if ($data['battery']['power'] > 0 && $data['battery']['power'] < 100) {
			if (($this->energy_max + $this->energy_used) > 0) {
				$data['battery']['tooltip'] .= 'Заряд: ' . Format::time(round(((round(250 * $this->getBuild('solar_plant')['level']) - $this->energy_ak) / ($this->energy_max + $this->energy_used)) * 3600));
			} elseif (($this->energy_max + $this->energy_used) < 0) {
				$data['battery']['tooltip'] .= 'Разряд: ' . Format::time(round(($this->energy_ak / abs($this->energy_max + $this->energy_used)) * 3600));
			}
		}

		return $data;
	}
}
