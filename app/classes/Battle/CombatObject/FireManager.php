<?php

namespace App\Battle\CombatObject;

use App\Battle\Utils\Iterable;

class FireManager extends Iterable
{
	protected $array = array();
	public function add(Fire $fire)
	{
		$this->array[] = $fire;
	}
	public function getAttackerTotalShots()
	{
		$tmp = 0;
		foreach ($this->array as $id => $fire)
		{
			$tmp += $fire->getAttackerTotalShots();
		}
		return $tmp;
	}
	public function getAttackerTotalFire()
	{
		$tmp = 0;
		foreach ($this->array as $id => $fire)
		{
			$tmp += $fire->getAttackerTotalFire();
		}
		return $tmp;
	}

}
