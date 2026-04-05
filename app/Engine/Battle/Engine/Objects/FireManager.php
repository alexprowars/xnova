<?php

namespace App\Engine\Battle\Engine\Objects;

class FireManager
{
	/** @var Fire[] */
	protected array $array = [];

	public function add(Fire $fire): void
	{
		$this->array[] = $fire;
	}

	/**
	 * @return Fire[]
	 */
	public function list(): array
	{
		return $this->array;
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
