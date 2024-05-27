<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Controller;
use App\Models\LogCredit;
use App\Models\PlanetEntity;
use App\Planet\Contracts\PlanetEntityProductionInterface;
use App\Vars;

class ResourcesController extends Controller
{
	private function buy($parse)
	{
		if ($this->user->vacation) {
			throw new ErrorException("Включен режим отпуска!");
		}

		if ($this->user->credits < 10) {
			throw new ErrorException('Для покупки вам необходимо еще ' . (10 - $this->user->credits) . ' кредитов');
		}

		if ($this->planet->merchand?->isFuture()) {
			throw new ErrorException('Покупать ресурсы можно только раз в 48 часов');
		}

		$this->planet->merchand = now()->addDays(2);

		foreach (Vars::getResources() as $res) {
			$this->planet->{$res} += $parse['buy_form'][$res];
		}

		$this->planet->update();

		$this->user->credits -= 10;
		$this->user->update();

		LogCredit::create([
			'user_id' => $this->user->id,
			'amount' => 10 * (-1),
			'type' => 2
		]);

		throw new RedirectException('Вы успешно купили ' . $parse['buy_form']['metal'] . ' металла, ' . $parse['buy_form']['crystal'] . ' кристалла, ' . $parse['buy_form']['deuterium'] . ' дейтерия', '/resources');
	}

	public function productionAction()
	{
		if ($this->user->vacation) {
			throw new PageException('Включен режим отпуска!');
		}

		$production = request('active', 'Y') == 'Y' ? 10 : 0;

		foreach ($this->user->planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction()->update();
		}

		$planetsId = $this->user->planets->pluck('id');

		$entityId = [4, 12, 212];

		foreach (Vars::getResources() as $res) {
			$entityId[] = Vars::getIdByName($res . '_mine');
		}

		PlanetEntity::query()
			->whereIn('planet_id', $planetsId)
			->whereIn('entity_id', $entityId)
			->update([
				'factor' => $production,
			]);

		$this->planet->reset();
		$this->planet->getProduction()->reset();
	}

	public function index()
	{
		if (Request::has('production')) {
			$this->productionAction();
		}

		if ($this->planet->planet_type == 3 || $this->planet->planet_type == 5) {
			foreach (Vars::getResources() as $res) {
				Config::set('settings.' . $res . '_basic_income', 0);
			}
		}

		if (Request::instance()->isMethod('post')) {
			if ($this->user->vacation) {
				throw new ErrorException("Включен режим отпуска!");
			}

			foreach (Request::post() as $field => $value) {
				if (!Vars::getIdByName($field)) {
					continue;
				}

				$value = max(0, min(10, (int) $value));

				if (Vars::getItemType($field) == Vars::ITEM_TYPE_BUILING || Vars::getItemType($field) == Vars::ITEM_TYPE_FLEET) {
					PlanetEntity::query()
						->whereIn('planet_id', $this->planet->id)
						->whereIn('entity_id', Vars::getIdByName($field))
						->update([
							'factor' => $value,
						]);
				}
			}

			$this->planet->reset();
			$this->planet->getProduction()->reset();
			$this->planet->getProduction()->update(true);
		}

		$parse = [];

		$parse['resources'] = Vars::getResources();

		$planetProduction = $this->planet->getProduction();

		$productionLevel = $planetProduction->getProductionFactor();

		$parse['buy_form'] = [
			'visible' => ($this->planet->planet_type == 1 && !$this->user->vacation),
			'time' => max(0, (int) now()->diffInSeconds($this->planet->merchand))
		];

		$parse['bonus_h'] = ($this->user->bonusValue('storage') - 1) * 100;
		$parse['items'] = [];

		foreach (Vars::getItemsByType('prod') as $productionId) {
			$entity = $this->planet->getEntity($productionId);

			if (!$entity || $this->planet->getLevel($productionId) <= 0 || !($entity instanceof PlanetEntityProductionInterface)) {
				continue;
			}

			$production = $entity->getProduction();
			$production->multiply($productionLevel / 100);

			$row = [];
			$row['id'] = $productionId;
			$row['factor'] = $entity->factor;
			$row['bonus'] = 0;

			if ($productionId == 4 || $productionId == 12) {
				$row['bonus'] += $this->user->bonusValue('energy');
				$row['bonus'] += ($this->user->getTechLevel('energy') * 2) / 100;
			} elseif ($productionId == 212) {
				$row['bonus'] += $this->user->bonusValue('solar');
			} elseif ($productionId == 1) {
				$row['bonus'] += $this->user->bonusValue('metal');
			} elseif ($productionId == 2) {
				$row['bonus'] += $this->user->bonusValue('crystal');
			} elseif ($productionId == 3) {
				$row['bonus'] += $this->user->bonusValue('deuterium');
			}

			$row['bonus'] = (int) (($row['bonus'] - 1) * 100);
			$row['level'] = $entity->amount;
			$row['resources'] = $production->toArray();
			$row['resources']['energy'] = $production->get($production::ENERGY);

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

			$parse['buy_form'][$res] = $row['production'] * 8;

			if ($parse['buy_form'][$res] < 0) {
				$parse['buy_form'][$res] = 0;
			}

			$parse['production'][$res] = $row;
		}

		if (Request::query('buy') && $this->planet->id > 0 && $this->planet->planet_type == 1) {
			$this->buy($parse);
		}

		$parse['production']['energy'] = [
			'basic' => (int) config('settings.energy_basic_income'),
			'capacity' => floor($this->planet->energy_max),
			'production' => floor(($this->planet->energy_max + config('settings.energy_basic_income')) + $this->planet->energy_used)
		];

		$parse['production_level'] = $productionLevel;
		$parse['energy_tech'] = $this->user->getTechLevel('energy');

		return $parse;
	}
}
