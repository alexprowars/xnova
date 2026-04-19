<?php

namespace App\Engine\Contracts;

interface EntityUnitInterface
{
	public function getMaxConstructible(): int;

	public function getAttack(): int;

	public function getArmor(): int;
}
