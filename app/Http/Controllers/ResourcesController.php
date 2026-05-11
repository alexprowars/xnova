<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\Resources;
use App\Engine\Objects\BaseObject;
use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\LogsCredit;
use App\Models\Planet;
use App\Models\PlanetEntity;
use App\Support\ToastType;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ResourcesController extends Controller
{
	public function index()
	{
		$result = [];
		$result['resources'] = Vars::getResources();

		$productionLevel = $this->planet->getProduction()
			->getProductionFactor();

		$result['buy_form'] = [
			'time' => max(0, (int) now()->diffInSeconds($this->planet->merchand)),
		] + $this->getBuyResourcesAmount();

		$result['bonus_h'] = (int) round(($this->user->bonus('storage') - 1) * 100);
		$result['items'] = [];

		$productions = $this->planet->getProduction()->getEnitityProductions();

		foreach ($productions as $entityId => $production) {
			$entity = $this->planet->getEntity($entityId);

			$production->multiply($productionLevel / 100, [Resources::ENERGY]);

			$item = [
				'id' => $entityId,
				'code' => Vars::getName($entityId),
				'factor' => $entity->getFactor(),
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

			$result['items'][] = $item;
		}

		$result['production_level'] = $productionLevel;

		return Inertia::render('Resources', $result);
	}

	public function buy(): void
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

		toast(ToastType::SUCCESS, 'Вы успешно купили ' . $resources['metal'] . ' металла, ' . $resources['crystal'] . ' кристалла, ' . $resources['deuterium'] . ' дейтерия');
	}

	public function shutdown(Request $request): void
	{
		$production = $request->post('active', 'Y') == 'Y' ? 10 : 0;

		foreach ($this->user->planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction()->update();
		}

		$this->user->planets->each(function (Planet $planet) use ($production) {
			$itemsId = collect(Vars::getObjectsByType([ItemType::BUILDING, ItemType::FLEET]))
				->filter(fn(BaseObject $object) => $object->getProduction() !== null)
				->pluck(fn(BaseObject $object) => $object->getId());

			$planet->entities->whereIn('entity_id', $itemsId)
				->each(function (PlanetEntity $entity) use ($production) {
					$entity->setFactor($production);
					$entity->save();
				});
		});
	}

	public function state(Request $request): void
	{
		$state = Arr::wrap($request->post('state', []));

		foreach ($state as $entityId => $value) {
			if (empty($entityId)) {
				continue;
			}

			if (!Vars::getItemObject($entityId)->getProduction()) {
				continue;
			}

			$value = max(0, min(10, (int) $value));

			$entity = $this->planet->getEntity($entityId);
			$entity->setFactor($value);
			$entity->save();
		}
	}

	protected function getBuyResourcesAmount(): array
	{
		if ($this->planet->user->isVacation()) {
			return [];
		}

		if ($this->planet->planet_type != PlanetType::PLANET) {
			return [];
		}

		$resources = new \App\Engine\Resources();
		$resources->add($this->planet->getProduction()->getBasicProduction());

		foreach (Vars::getItemsByType([ItemType::BUILDING, ItemType::FLEET]) as $itemId) {
			$entity = $this->planet->getEntityUnit($itemId);

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
