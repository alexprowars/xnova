<?php

namespace Xnova;

use Phalcon\Di;
use Phalcon\Registry;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class Vars
{
	/**
	 * @var bool|Registry
	 */
	private static $registry = false;

	const ITEM_TYPE_BUILING = 'build';
	const ITEM_TYPE_TECH = 'tech';
	const ITEM_TYPE_TECH_FLEET = 'tech_f';
	const ITEM_TYPE_FLEET = 'fleet';
	const ITEM_TYPE_DEFENSE = 'defense';
	const ITEM_TYPE_OFFICIER = 'officier';

	static function init ()
	{
		require_once(dirname(__DIR__).'/Vars/main.php');

		self::$registry = Di::getDefault()->getShared('registry');

		/** @var array $resource */
		/** @var array $requeriments */
		/** @var array $pricelist */
		/** @var array $gun_armour */
		/** @var array $CombatCaps */
		/** @var array $ProdGrid */
		/** @var array $reslist */

		self::$registry->resource = $resource;
		self::$registry->resource_flip = array_flip($resource);
		self::$registry->requeriments = $requeriments;
		self::$registry->pricelist = $pricelist;
		self::$registry->gun_armour = $gun_armour;
		self::$registry->CombatCaps = $CombatCaps;
		self::$registry->ProdGrid = $ProdGrid;
		self::$registry->reslist = $reslist;
	}

	static function getName ($id)
	{
		if (isset(self::$registry->resource[$id]))
			self::$registry->resource[$id];

		return false;
	}

	static function getItemPrice ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry->resource_flip[$itemId];

		if (isset(self::$registry->pricelist[$itemId]))
			return self::$registry->pricelist[$itemId];

		return [];
	}

	static function getItemTotalPrice ($itemId, $allResources = false)
	{
		$price = self::getItemPrice($itemId);

		if (!$allResources)
			return $price['metal'] + $price['crystal'];
		else
			return $price['metal'] + $price['crystal'] + $price['deuterium'];
	}

	static function getItemType ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry->resource_flip[$itemId];

		if (in_array($itemId, self::$registry->reslist['build']))
			return self::ITEM_TYPE_BUILING;
		if (in_array($itemId, self::$registry->reslist['tech']))
			return self::ITEM_TYPE_TECH;
		if (in_array($itemId, self::$registry->reslist['tech_f']))
			return self::ITEM_TYPE_TECH_FLEET;
		if (in_array($itemId, self::$registry->reslist['fleet']))
			return self::ITEM_TYPE_FLEET;
		if (in_array($itemId, self::$registry->reslist['defense']))
			return self::ITEM_TYPE_DEFENSE;
		if (in_array($itemId, self::$registry->reslist['officier']))
			return self::ITEM_TYPE_OFFICIER;

		return false;
	}

	static function getItemRequeriments ($itemId)
	{
		if (self::$registry === false)
			self::init();

		if (!is_numeric($itemId))
			$itemId = self::$registry->resource_flip[$itemId];

		if (isset(self::$registry->requeriments[$itemId]))
			return self::$registry->requeriments[$itemId];

		return [];
	}

	static function getItemsByType ($types)
	{
		if (!is_array($types))
			$types = [$types];

		$result = [];

		foreach ($types as $type)
		{
			if (isset(self::$registry->reslist[$type]))
				$result = array_merge($result, self::$registry->reslist[$type]);
		}

		return $result;
	}
}