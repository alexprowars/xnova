<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\LogsCredit;
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
			'time' => max(0, (int) now()->diffInSeconds($this->planet->merchand)),
		] + $this->getBuyResourcesAmount();

		$parse['bonus_h'] = (int) round(($this->user->bonus('storage') - 1) * 100);
		$parse['items'] = [];

		$productions = $this->planet->getProduction()->getEnitityProductions();

		foreach ($productions as $entityId => $production) {
			$entity = $this->planet->getEntity($entityId);

			$production->multiply($productionLevel / 100, [Resources::ENERGY]);

			$item = [
				'id' => $entityId,
				'code' => Vars::getName($entityId),
				'factor' => $entity->factor ?? 10,
				'level' => $entity->amount,
				'bonus' => 0,
			];

			if ($entityId == 4 || $entityId == 12) {
				$item['bonus'] += $this->user->bonus('energy');
				$item['bonus'] += ($this->user->getTechLevel('energy') * 2) / 100;
			} elseif ($entityId == 212) {
				$item['bonus'] += $this->user->bonus('solar');
			} elseif ($entityId == 1) {
				$item['bonus'] += $this->user->bonus('metal');
			} elseif ($entityId == 2) {
				$item['bonus'] += $this->user->bonus('crystal');
			} elseif ($entityId == 3) {
				$item['bonus'] += $this->user->bonus('deuterium');
			}

			$item['bonus'] = (int) (($item['bonus'] - 1) * 100);
			$item['resources'] = $production->toArray();
			$item['resources']['energy'] = $production->get(Resources::ENERGY);

			$parse['items'][] = $item;
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

		LogsCredit::create([
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
		if ($this->planet->user->isVacation()) {
			return [];
		}

		if ($this->planet->planet_type != PlanetType::PLANET) {
			return [];
		}

		$resources = new \App\Engine\Resources();
		$resources->add($this->planet->getProduction()->getBasicProduction());

		foreach (Vars::getItemsByType(ItemType::PRODUCTION) as $itemId) {
			$entity = $this->planet->getEntity($itemId)->unit();

			if (!$entity || $entity->getLevel() <= 0) {
				continue;
			}

			$resources->add($entity->getProduction(10));
		}

		$result = [];

		foreach (Vars::getResources() as $res) {
			$result[$res] = max(0, $resources->get($res) * 8);
		}

		return $result;
	}
}
