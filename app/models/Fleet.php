<?php
namespace App\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Mvc\Model;

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

	public function getSource()
	{
		return DB_PREFIX."fleets";
	}

	public function getStartAdressLink ($FleetType = '')
	{
		$link  = "<a href=\"/galaxy/?r=3&amp;galaxy=" . $this->start_galaxy . "&amp;system=" . $this->start_system . "\" " . $FleetType . " >";
		$link .= "[" . $this->start_galaxy . ":" . $this->start_system . ":" . $this->start_planet . "]</a>";

		return $link;
	}

	public function getTargetAdressLink ($FleetType = '')
	{
		$link  = "<a href=\"/galaxy/?r=3&amp;galaxy=" . $this->end_galaxy . "&amp;system=" . $this->end_system . "\" " . $FleetType . " >";
		$link .= "[" . $this->end_galaxy . ":" . $this->end_system . ":" . $this->end_planet . "]</a>";

		return $link;
	}
}