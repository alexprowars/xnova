<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\LogCredit;
use App\Models\PlanetEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ResourcesController extends Controller
{
	public function index()
	{
		$parse = [];
		$parse['resources'] = Vars::getResources();

		$productionLevel = $this->planet->getProduction()
			->getProductionFactor();

		$parse['buy_form'] = [
			'visible' => ($this->planet->planet_type == PlanetType::PLANET && !$this->user->isVacation()),
			'time' => max(0, (int) now()->diffInSeconds($this->planet->merchand)),
		] + $this->getBuyResourcesAmount();

		$parse['bonus_h'] = (int) round(($this->user->bonus('storage') - 1) * 100);
		$parse['items'] = [];

		foreach (Vars::getItemsByType(ItemType::PRODUCTION) as $entityId) {
			$entity = $this->planet->getEntity($entityId)->unit();

			if (!$entity) {
				continue;
			}

			$planetEntity = $this->planet->entities->where('entity_id', $entityId)->first();

			$production = $entity->getProduction();
			$production->multiply($productionLevel / 100, [Resources::ENERGY]);

			$row = [];
			$row['id'] = $entityId;
			$row['code'] = Vars::getName($entityId);
			$row['factor'] = $planetEntity->factor ?? 10;
			$row['bonus'] = 0;

			if ($entityId == 4 || $entityId == 12) {
				$row['bonus'] += $this->user->bonus('energy');
				$row['bonus'] += ($this->user->getTechLevel('energy') * 2) / 100;
			} elseif ($entityId == 212) {
				$row['bonus'] += $this->user->bonus('solar');
			} elseif ($entityId == 1) {
				$row['bonus'] += $this->user->bonus('metal');
			} elseif ($entityId == 2) {
				$row['bonus'] += $this->user->bonus('crystal');
			} elseif ($entityId == 3) {
				$row['bonus'] += $this->user->bonus('deuterium');
			}

			$row['bonus'] = (int) (($row['bonus'] - 1) * 100);
			$row['level'] = $entity->getLevel();
			$row['resources'] = $production->toArray();
			$row['resources']['energy'] = $production->get(Resources::ENERGY);

			$parse['items'][] = $row;
		}

		$parse['production_level'] = $productionLevel;

		return $parse;
	}

	public function buy()
	{
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

		return $resources;
	}

	public function shutdown(Request $request)
	{
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
		$state = Arr::wrap($request->post('state', []));

		foreach ($state as $entityId => $value) {
			if (empty($entityId) || !in_array($entityId, Vars::getItemsByType(ItemType::PRODUCTION))) {
				continue;
			}

			$value = max(0, min(10, (int) $value));

			$this->planet->entities()
				->where('entity_id', $entityId)
				->update(['factor' => $value]);
		}
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
