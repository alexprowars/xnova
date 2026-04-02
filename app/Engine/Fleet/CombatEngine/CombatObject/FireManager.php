<?php

namespace App\Engine\Fleet\CombatEngine\CombatObject;

use App\Engine\Fleet\CombatEngine\Utils\IterableIterator;

/**
 * @method Fire[] getIterator()
 * @property Fire[] $array
 */
class FireManager extends IterableIterator
{
	public function add(Fire $fire): void
	{
		$this->array[] = $fire;
	}

	public function getAttackerTotalShots(): int
	{
		$tmp = 0;

		foreach ($this->array as $fire) {
			$tmp += $fire->getAttackerTotalShots();
		}

		return $tmp;
	}
	public function getAttackerTotalFire(): int
	{
		$tmp = 0;

		foreach ($this->array as $fire) {
			$tmp += $fire->getAttackerTotalFire();
		}

		return $tmp;
	}
}
