<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Fleet[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Fleet findFirst(mixed $parameters = null)
 */
class Fleet extends Model
{
	public $id;
	public $owner;
	public $owner_name;
	public $mission;
	public $amount;
	public $fleet_array;

	public $start_time;
	public $start_galaxy;
	public $start_system;
	public $start_planet;
	public $start_type;

	public $end_time;
	public $end_stay;
	public $end_galaxy;
	public $end_system;
	public $end_planet;
	public $end_type;

	public $resource_metal;
	public $resource_crystal;
	public $resource_deuterium;

	public $target_owner;
	public $target_owner_name;
	public $group_id;
	public $mess;
	public $create_time;
	public $update_time;
	public $raunds;
	public $won;

	public $username = '';

	public function onConstruct()
	{
		$this->useDynamicUpdate(true);
	}

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());
	}

	public function getSource()
	{
		return DB_PREFIX."fleets";
	}

	public function splitStartPosition ()
	{
		return $this->start_galaxy.':'.$this->start_system.':'.$this->start_planet;
	}

	public function splitTargetPosition ()
	{
		return $this->end_galaxy.':'.$this->end_system.':'.$this->end_planet;
	}

	public function getStartAdressLink ($FleetType = '')
	{
		$uri = '/galaxy/?galaxy='.$this->start_galaxy.'&system='.$this->start_system;

		if ($this->getDI()->has('url'))
			$uri = $this->getDI()->getShared('url')->get($uri);

		return '<a href="'.$uri.'" '.$FleetType.'>['.$this->splitStartPosition().']</a>';
	}

	public function getTargetAdressLink ($FleetType = '')
	{
		$uri = '/galaxy/?galaxy='.$this->end_galaxy.'&system='.$this->end_system;

		if ($this->getDI()->has('url'))
			$uri = $this->getDI()->getShared('url')->get($uri);

		return '<a href="'.$uri.'" '.$FleetType.'>['.$this->splitTargetPosition().']</a>';
	}

	public function getTotalShips ()
	{
		$result = 0;

		$data = $this->getShips();

		foreach ($data as $type)
			$result += $type['count'];

		return $result;
	}

	public function getShips ($fleets = false)
	{
		if (!$fleets)
			$fleets = $this->fleet_array;

		$result = [];

		if (!is_array($fleets))
			$fleets = json_decode($fleets, true);

		if (!is_array($fleets))
			return [];

		foreach ($fleets as $fleet)
		{
			if (!isset($fleet['id']))
				continue;

			$fleetId = (int) $fleet['id'];

			$result[$fleetId] = [
				'id' => $fleetId,
				'count' => isset($fleet['count']) ? (int) $fleet['count'] : 0
			];

			if (isset($fleet['target']))
				$result[$fleetId]['target'] = (int) $fleet['target'];
		}

		return $result;
	}

	public function beforeSave ()
	{
		if (is_array($this->fleet_array))
			$this->fleet_array = json_encode(array_values($this->fleet_array));
	}

	public function canBack ()
	{
		return ($this->mess == 0 || ($this->mess == 3 && $this->mission != 15) && $this->mission != 20 && $this->target_owner != 1);
	}
}