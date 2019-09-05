<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class Vars
{
	private static $registry = [];

	const ITEM_TYPE_BUILING = 'build';
	const ITEM_TYPE_TECH = 'tech';
	const ITEM_TYPE_FLEET = 'fleet';
	const ITEM_TYPE_DEFENSE = 'defense';
	const ITEM_TYPE_OFFICIER = 'officier';

	static function init ()
	{
		require_once(app_path('Vars/main.php'));

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

	static function getStorage (): array
	{
		return self::$registry;
	}

	/**
	 * @param $id
	 * @return string|bool
	 */
	static function getName ($id)
	{
		return self::$registry['resource'][$id] ?? false;
	}

	/**
	 * @param $name
	 * @return int|bool
	 */
	static function getIdByName ($name)
	{
		return self::$registry['resource_flip'][$name] ?? false;
	}

	/**
	 * @param $itemId
	 * @return array
	 */
	static function getItemPrice ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry['resource_flip'][$itemId];

		return self::$registry['pricelist'][$itemId] ?? [];
	}

	/**
	 * @param $itemId
	 * @param bool $allResources
	 * @return int
	 */
	static function getItemTotalPrice ($itemId, $allResources = false)
	{
		$price = self::getItemPrice($itemId);

		if (!count($price))
			return 0;

		if (!$allResources)
			return $price['metal'] + $price['crystal'];
		else
			return $price['metal'] + $price['crystal'] + $price['deuterium'];
	}

	/**
	 * @param $itemId
	 * @return bool|string
	 */
	static function getItemType ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry['resource_flip'][$itemId];

		if (in_array($itemId, self::$registry['reslist']['build']))
			return self::ITEM_TYPE_BUILING;
		if (in_array($itemId, self::$registry['reslist']['tech']))
			return self::ITEM_TYPE_TECH;
		if (in_array($itemId, self::$registry['reslist']['fleet']))
			return self::ITEM_TYPE_FLEET;
		if (in_array($itemId, self::$registry['reslist']['defense']))
			return self::ITEM_TYPE_DEFENSE;
		if (in_array($itemId, self::$registry['reslist']['officier']))
			return self::ITEM_TYPE_OFFICIER;

		return false;
	}

	/**
	 * @param $itemId
	 * @return array
	 */
	static function getItemRequirements ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry['resource_flip'][$itemId];

		return self::$registry['requeriments'][$itemId] ?? [];
	}

	/**
	 * @param $types
	 * @return array
	 */
	static function getItemsByType ($types)
	{
		if (!is_array($types))
			$types = [$types];

		$result = [];

		foreach ($types as $type)
		{
			if (isset(self::$registry['reslist'][$type]))
				$result = array_merge($result, self::$registry['reslist'][$type]);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	static function getResources ()
	{
		return self::$registry['reslist']['res'];
	}

	static function getUnitData (int $unitId)
	{
		return self::$registry['CombatCaps'][$unitId] ?? false;
	}

	static function getBuildProduction ($buildId)
	{
		return self::$registry['ProdGrid'][$buildId] ?? false;
	}

	/**
	 * @param $planetType
	 * @return array
	 */
	static function getAllowedBuilds ($planetType)
	{
		if (!isset(self::$registry['reslist']['allowed'][(int) $planetType]))
			return [];

		return self::$registry['reslist']['allowed'][(int) $planetType];
	}
}