<?php

namespace App\Battle\CombatObject;

use App\Battle\Utils\Iterable;

/**
 * Class FireManager
 * @package App\Battle\CombatObject
 * @method Fire[] getIterator
 */
class FireManager extends Iterable
{
	/**
	 * @var Fire[] $array
	 */
	protected $array = array();

	public function add(Fire $fire)
	{
		$this->array[] = $fire;
	}

	public function getAttackerTotalShots()
	{
		$tmp = 0;

		foreach ($this->array as $id => $fire)
			$tmp += $fire->getAttackerTotalShots();

		return $tmp;
	}
	public function getAttackerTotalFire()
	{
		$tmp = 0;

		foreach ($this->array as $id => $fire)
			$tmp += $fire->getAttackerTotalFire();

		return $tmp;
	}
}
