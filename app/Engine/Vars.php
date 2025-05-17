<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use Illuminate\Support\Arr;

class Vars
{
	private static array $registry;

	public static function init()
	{
		require(resource_path('engine/main.php'));

		/** @var array $resource */
		/** @var array $requeriments */
		/** @var array $pricelist */
		/** @var array $gun_armour */
		/** @var array $CombatCaps */
		/** @var array $ProdGrid */
		/** @var array $reslist */

		self::$registry['resource'] = $resource;
		self::$registry['resource_flip'] = array_flip($resource);
		self::$registry['requeriments'] = $requeriments;
		self::$registry['pricelist'] = $pricelist;
		self::$registry['gun_armour'] = $gun_armour;
		self::$registry['CombatCaps'] = $CombatCaps;
		self::$registry['ProdGrid'] = $ProdGrid;
		self::$registry['reslist'] = $reslist;
	}

	public static function getStorage(): array
	{
		return self::$registry;
	}

	public static function updateStorage(string $key, mixed $value)
	{
		Arr::set(self::$registry, $key, $value);
	}

	public static function getName($id): ?string
	{
		return self::$registry['resource'][$id] ?? null;
	}

	public static function getIdByName($name): ?int
	{
		return self::$registry['resource_flip'][$name] ?? null;
	}

	public static function getItemPrice($itemId): array
	{
		if (empty(self::$registry)) {
			self::init();
		}

		if (!is_numeric($itemId)) {
			$itemId = self::$registry['resource_flip'][$itemId];
		}

		return self::$registry['pricelist'][$itemId] ?? [];
	}

	public static function getItemTotalPrice($itemId, $all = false): int
	{
		$price = self::getItemPrice($itemId);

		if (!count($price)) {
			return 0;
		}

		if (!$all) {
			return $price['metal'] + $price['crystal'];
		} else {
			return $price['metal'] + $price['crystal'] + $price['deuterium'];
		}
	}

	public static function getItemType($itemId): ?ItemType
	{
		if (empty(self::$registry)) {
			self::init();
		}

		if (!is_numeric($itemId)) {
			$itemId = self::$registry['resource_flip'][$itemId];
		}

		if (in_array($itemId, self::$registry['reslist']['build'])) {
			return ItemType::BUILDING;
		}
		if (in_array($itemId, self::$registry['reslist']['tech'])) {
			return ItemType::TECH;
		}
		if (in_array($itemId, self::$registry['reslist']['fleet'])) {
			return ItemType::FLEET;
		}
		if (in_array($itemId, self::$registry['reslist']['defense'])) {
			return ItemType::DEFENSE;
		}
		if (in_array($itemId, self::$registry['reslist']['officier'])) {
			return ItemType::OFFICIER;
		}

		return null;
	}

	public static function getItemRequirements($itemId): array
	{
		if (empty(self::$registry)) {
			self::init();
		}

		if (!is_numeric($itemId)) {
			$itemId = self::$registry['resource_flip'][$itemId];
		}

		return self::$registry['requeriments'][$itemId] ?? [];
	}

	public static function getItemsByType(array | ItemType $types): array
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		$result = [];

		foreach ($types as $type) {
			if (isset(self::$registry['reslist'][$type->value])) {
				$result = array_merge($result, self::$registry['reslist'][$type->value]);
			}
		}

		return $result;
	}

	public static function getResources(): array
	{
		return self::$registry['reslist']['res'];
	}

	public static function getUnitData(int $unitId): ?array
	{
		return self::$registry['CombatCaps'][$unitId] ?? null;
	}

	public static function getBuildProduction($buildId): ?array
	{
		return self::$registry['ProdGrid'][$buildId] ?? null;
	}

	public static function getAllowedBuilds(PlanetType $planetType): array
	{
		return self::$registry['reslist']['allowed'][$planetType->value] ?? [];
	}
}
