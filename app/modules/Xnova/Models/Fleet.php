<?php

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
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
		if ($this->getDI()->has('url'))
			$uri = $this->getDI()->getShared('url')->getBaseUri();
		else
			$uri = '#BASEPATH#';

		return '<a href="'.$uri.'galaxy/'.$this->start_galaxy.'/'.$this->start_system.'/" '.$FleetType.'>['.$this->splitStartPosition().']</a>';
	}

	public function getTargetAdressLink ($FleetType = '')
	{
		if ($this->getDI()->has('url'))
			$uri = $this->getDI()->getShared('url')->getBaseUri();
		else
			$uri = '#BASEPATH#';

		return '<a href="'.$uri.'galaxy/'.$this->end_galaxy.'/'.$this->end_system.'/" '.$FleetType.'>['.$this->splitTargetPosition().']</a>';
	}

	public function getTotalShips ()
	{
		$result = 0;

		$data = $this->getShips();

		foreach ($data as $type)
			$result += $type['cnt'];

		return $result;
	}

	public function getShips ($fleetAmount = '')
	{
		if (!$fleetAmount)
			$fleetAmount = $this->fleet_array;

		$fleetTyps = explode(';', $fleetAmount);

		$fleetAmount = [];

		foreach ($fleetTyps as $fleetTyp)
		{
			$temp = explode(',', $fleetTyp);

			if (empty($temp[0]))
				continue;

			if (!isset($fleetAmount[$temp[0]]))
				$fleetAmount[$temp[0]] = ['cnt' => 0, 'lvl' => 0];

			$lvl = explode("!", $temp[1]);

			$fleetAmount[$temp[0]]['cnt'] += $lvl[0];

			if (isset($lvl[1]))
				$fleetAmount[$temp[0]]['lvl'] = $lvl[1];
		}

		return $fleetAmount;
	}
}