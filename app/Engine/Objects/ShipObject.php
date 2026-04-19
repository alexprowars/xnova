<?php

namespace App\Engine\Objects;

class ShipObject extends DefenceObject
{
	public function getConsumption(): int
	{
		return $this->data['combat']['consumption'] ?? 0;
	}

	public function getStayConsumption(): int
	{
		return $this->data['combat']['stay'] ?? 0;
	}

	public function getSpeed(): int
	{
		return $this->data['combat']['speed'] ?? 0;
	}

	public function getCapacity(): int
	{
		return $this->data['combat']['capacity'] ?? 0;
	}

	public function getEngineType(): ?int
	{
		return $this->data['combat']['type_engine'] ?? null;
	}

	public function getEngineUpgrade(): ?array
	{
		return $this->data['combat']['engine_up'] ?? null;
	}

	public function upgradeEngine(): void
	{
		$upgrade = $this->getEngineUpgrade();

		$this->data['combat']['type_engine'] = $upgrade['engine'];
		$this->data['combat']['speed'] = $upgrade['speed'];
	}
}
