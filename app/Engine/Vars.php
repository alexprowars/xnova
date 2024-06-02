<?php

namespace App\Engine;

use App\Engine\Enums\PlanetType;

class Vars
{
	private static $registry;

	public const ITEM_TYPE_BUILING = 'build';
	public const ITEM_TYPE_TECH = 'tech';
	public const ITEM_TYPE_FLEET = 'fleet';
	public const ITEM_TYPE_DEFENSE = 'defense';
	public const ITEM_TYPE_OFFICIER = 'officier';

	public static function init()
	{
		require_once(resource_path('engine/main.php'));

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

	public static function getItemTotalPrice($itemId, $allResources = false): int
	{
		$price = self::getItemPrice($itemId);

		if (!count($price)) {
			return 0;
		}

		if (!$allResources) {
			return $price['metal'] + $price['crystal'];
		} else {
			return $price['metal'] + $price['crystal'] + $price['deuterium'];
		}
	}

	public static function getItemType($itemId): ?string
	{
		if (empty(self::$registry)) {
			self::init();
		}

		if (!is_numeric($itemId)) {
			$itemId = self::$registry['resource_flip'][$itemId];
		}

		if (in_array($itemId, self::$registry['reslist']['build'])) {
			return self::ITEM_TYPE_BUILING;
		}
		if (in_array($itemId, self::$registry['reslist']['tech'])) {
			return self::ITEM_TYPE_TECH;
		}
		if (in_array($itemId, self::$registry['reslist']['fleet'])) {
			return self::ITEM_TYPE_FLEET;
		}
		if (in_array($itemId, self::$registry['reslist']['defense'])) {
			return self::ITEM_TYPE_DEFENSE;
		}
		if (in_array($itemId, self::$registry['reslist']['officier'])) {
			return self::ITEM_TYPE_OFFICIER;
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

	public static function getItemsByType($types): array
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		$result = [];

		foreach ($types as $type) {
			if (isset(self::$registry['reslist'][$type])) {
				$result = array_merge($result, self::$registry['reslist'][$type]);
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
