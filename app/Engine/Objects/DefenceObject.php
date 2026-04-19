<?php

namespace App\Engine\Objects;

class DefenceObject extends BaseObject
{
	public function getAttack(): int
	{
		return $this->data['combat']['attack'] ?? 0;
	}

	public function getShield(): int
	{
		return $this->data['combat']['shield'] ?? 0;
	}

	public function getArmor(): int
	{
		return (int) floor($this->getTotalPrice() / 10);
	}

	public function getRapidfire(): array
	{
		return $this->data['combat']['rapidfire'] ?? [];
	}

	public function getWeaponType(): ?int
	{
		return $this->data['combat']['type_gun'] ?? null;
	}

	public function getArmorType(): ?int
	{
		return $this->data['combat']['type_armor'] ?? null;
	}
}
