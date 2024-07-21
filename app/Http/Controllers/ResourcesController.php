<?php

namespace App\Http\Controllers;

use App\Engine\Contracts\EntityProductionInterface;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Models\LogCredit;
use App\Models\PlanetEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ResourcesController extends Controller
{
	public function buy()
	{
		if ($this->user->isVacation()) {
			throw new Exception('Включен режим отпуска!');
		}

		if (!$this->planet->id || $this->planet->planet_type != PlanetType::PLANET) {
			throw new Exception('На этой планете нельзя купить ресурсы');
		}

		if ($this->user->credits < 10) {
			throw new Exception('Для покупки вам необходимо еще ' . (10 - $this->user->credits) . ' кредитов');
		}

		if ($this->planet->merchand?->isFuture()) {
			throw new Exception('Покупать ресурсы можно только раз в 48 часов');
		}

		$this->planet->merchand = now()->addDays(2);

		$resources = $this->getBuyResourcesAmount();

		foreach (Vars::getResources() as $res) {
			$this->planet->{$res} += $resources[$res];
		}

		$this->planet->update();

		$this->user->credits -= 10;
		$this->user->update();

		LogCredit::create([
			'user_id' => $this->user->id,
			'amount' => 10 * (-1),
			'type' => 2,
		]);

		throw new RedirectException('/resources', 'Вы успешно купили ' . $resources['metal'] . ' металла, ' . $resources['crystal'] . ' кристалла, ' . $resources['deuterium'] . ' дейтерия');
	}

	public function shutdown(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new PageException('Включен режим отпуска!');
		}

		$production = $request->post('active', 'Y') == 'Y' ? 10 : 0;

		foreach ($this->user->planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction()->update();
		}

		PlanetEntity::query()
			->whereIn('planet_id', $this->user->planets->pluck('id'))
			->whereIn('entity_id', Vars::getItemsByType(ItemType::PRODUCTION))
			->update(['factor' => $production]);
	}

	public function state(Request $request)
	{
		if ($this->user->isVacation()) {
			throw new Exception('Включен режим отпуска!');
		}

		foreach ($request->post('state') as $entityId => $value) {
			if (empty($entityId) || !in_array($entityId, Vars::getItemsByType(ItemType::PRODUCTION))) {
				continue;
			}

			$value = max(0, min(10, (int) $value));

			$this->planet->entities()
				->where('entity_id', $entityId)
				->update(['factor' => $value]);
		}
	}

	public function index()
	{
		if ($this->planet->planet_type == PlanetType::MOON || $this->planet->planet_type == PlanetType::MILITARY_BASE) {
			foreach (Vars::getResources() as $res) {
				Config::set('settings.' . $res . '_basic_income', 0);
			}
		}

		$parse = [];
		$parse['resources'] = Vars::getResources();

		$planetProduction = $this->planet->getProduction();

		$productionLevel = $planetProduction->getProductionFactor();

		$parse['buy_form'] = [
			'visible' => ($this->planet->planet_type == PlanetType::PLANET && !$this->user->isVacation()),
			'time' => max(0, (int) now()->diffInSeconds($this->planet->merchand)),
		] + $this->getBuyResourcesAmount();

		$parse['bonus_h'] = ($this->user->bonus('storage') - 1) * 100;
		$parse['items'] = [];

		foreach (Vars::getItemsByType(ItemType::PRODUCTION) as $productionId) {
			$entity = $this->planet->getEntity($productionId)->unit();

			if (!$entity || $this->planet->getLevel($productionId) <= 0 || !($entity instanceof EntityProductionInterface)) {
				continue;
			}

			$planetEntity = $this->planet->entities->where('entity_id', $productionId)->first();

			$production = $entity->getProduction();
			$production->multiply($productionLevel / 100);

			$row = [];
			$row['id'] = $productionId;
			$row['factor'] = $planetEntity?->factor ?? 10;
			$row['bonus'] = 0;

			if ($productionId == 4 || $productionId == 12) {
				$row['bonus'] += $this->user->bonus('energy');
				$row['bonus'] += ($this->user->getTechLevel('energy') * 2) / 100;
			} elseif ($productionId == 212) {
				$row['bonus'] += $this->user->bonus('solar');
			} elseif ($productionId == 1) {
				$row['bonus'] += $this->user->bonus('metal');
			} elseif ($productionId == 2) {
				$row['bonus'] += $this->user->bonus('crystal');
			} elseif ($productionId == 3) {
				$row['bonus'] += $this->user->bonus('deuterium');
			}

			$row['bonus'] = (int) (($row['bonus'] - 1) * 100);
			$row['level'] = $entity->level;
			$row['resources'] = $production->toArray();
			$row['resources']['energy'] = $production->get(Resources::ENERGY);

			$parse['items'][] = $row;
		}

		$parse['production'] = [];

		$storage = $planetProduction->getStorageCapacity();
		$production = $planetProduction->getResourceProduction();

		foreach (Vars::getResources() as $res) {
			$row = [];
			$row['capacity'] = $storage->get($res);
			$row['production'] = $production->get($res);
			$row['storage'] = floor($this->planet->{$res} / $storage->get($res) * 100);

			$parse['production'][$res] = $row;
		}

		$parse['production']['energy'] = [
			'basic' => (int) config('game.energy_basic_income'),
			'capacity' => floor($this->planet->energy_max),
			'production' => floor(($this->planet->energy_max + config('game.energy_basic_income')) + $this->planet->energy_used),
		];

		$parse['production_level'] = $productionLevel;
		$parse['energy_tech'] = $this->user->getTechLevel('energy');

		return response()->state($parse);
	}

	protected function getBuyResourcesAmount()
	{
		$production = $this->planet->getProduction()
			->getResourceProduction();

		$resources = [];

		foreach (Vars::getResources() as $res) {
			$resources[$res] = max(0, $production->get($res) * 8);
		}

		return $resources;
	}
}
