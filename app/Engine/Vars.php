<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use Illuminate\Support\Arr;

class Vars
{
	/** @var array<string, array> */
	protected array $registry;

	public function __construct()
	{
		$this->init();
	}

	protected function init()
	{
		require(resource_path('engine/main.php'));

		/** @var array<int, string> $resource */
		/** @var array<int, array> $requeriments */
		/** @var array<int, array> $pricelist */
		/** @var array<int, array> $gun_armour */
		/** @var array<int, array> $CombatCaps */
		/** @var array<int, array> $ProdGrid */
		/** @var array<string, array> $reslist */

		$this->registry['resource'] = $resource;
		$this->registry['resource_flip'] = array_flip($resource);
		$this->registry['requeriments'] = $requeriments;
		$this->registry['pricelist'] = $pricelist;
		$this->registry['gun_armour'] = $gun_armour;
		$this->registry['CombatCaps'] = $CombatCaps;
		$this->registry['ProdGrid'] = $ProdGrid;
		$this->registry['reslist'] = $reslist;
	}

	public function getStorage(): array
	{
		return $this->registry;
	}

	public function updateStorage(string $key, mixed $value)
	{
		Arr::set($this->registry, $key, $value);
	}

	public function getName($id): ?string
	{
		return $this->registry['resource'][$id] ?? null;
	}

	public function getIdByName($name): ?int
	{
		return $this->registry['resource_flip'][$name] ?? null;
	}

	public function getItemPrice($itemId): array
	{
		if (!is_numeric($itemId)) {
			$itemId = $this->registry['resource_flip'][$itemId];
		}

		return $this->registry['pricelist'][$itemId] ?? [];
	}

	public function getItemTotalPrice($itemId, $all = false): int
	{
		$price = $this->getItemPrice($itemId);

		if (!count($price)) {
			return 0;
		}

		if (!$all) {
			return $price['metal'] + $price['crystal'];
		} else {
			return $price['metal'] + $price['crystal'] + $price['deuterium'];
		}
	}

	public function getItemType($itemId): ?ItemType
	{
		if (!is_numeric($itemId)) {
			$itemId = $this->registry['resource_flip'][$itemId];
		}

		if (in_array($itemId, $this->registry['reslist']['build'])) {
			return ItemType::BUILDING;
		}
		if (in_array($itemId, $this->registry['reslist']['tech'])) {
			return ItemType::TECH;
		}
		if (in_array($itemId, $this->registry['reslist']['fleet'])) {
			return ItemType::FLEET;
		}
		if (in_array($itemId, $this->registry['reslist']['defense'])) {
			return ItemType::DEFENSE;
		}
		if (in_array($itemId, $this->registry['reslist']['officier'])) {
			return ItemType::OFFICIER;
		}

		return null;
	}

	public function getItemRequirements($itemId): array
	{
		if (!is_numeric($itemId)) {
			$itemId = $this->registry['resource_flip'][$itemId];
		}

		return $this->registry['requeriments'][$itemId] ?? [];
	}

	public function getItemsByType(array | ItemType $types): array
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		$result = [];

		foreach ($types as $type) {
			if (isset($this->registry['reslist'][$type->value])) {
				$result = array_merge($result, $this->registry['reslist'][$type->value]);
			}
		}

		return $result;
	}

	public function getResources(): array
	{
		return $this->registry['reslist']['res'];
	}

	public function getUnitData(int $unitId): ?array
	{
		return $this->registry['CombatCaps'][$unitId] ?? null;
	}

	public function getBuildProduction($buildId): ?array
	{
		return $this->registry['ProdGrid'][$buildId] ?? null;
	}

	public function getAllowedBuilds(PlanetType $planetType): array
	{
		return $this->registry['reslist']['allowed'][$planetType->value] ?? [];
	}
}
