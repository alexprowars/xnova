<?php

namespace App\Engine\CombatEngine\CombatObject;

use App\Engine\CombatEngine\Utils\IterableIterator;

/**
 * @method Fire[] getIterator()
 * @property Fire[] $array
 */
class FireManager extends IterableIterator
{

	public function add(Fire $fire)
	{
		$this->array[] = $fire;
	}

	public function getAttackerTotalShots()
	{
		$tmp = 0;

		foreach ($this->array as $id => $fire) {
			$tmp += $fire->getAttackerTotalShots();
		}

		return $tmp;
	}
	public function getAttackerTotalFire()
	{
		$tmp = 0;

		foreach ($this->array as $id => $fire) {
			$tmp += $fire->getAttackerTotalFire();
		}

		return $tmp;
	}
}
