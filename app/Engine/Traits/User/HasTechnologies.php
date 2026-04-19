<?php

namespace App\Engine\Traits\User;

use App\Engine\Entity\Model\TechnologiesEntity;
use App\Engine\Enums\ItemType;
use App\Facades\Vars;

trait HasTechnologies
{
	public function getTech(int|string $techId): ?TechnologiesEntity
	{
		if (!is_numeric($techId)) {
			$techId = Vars::getIdByName($techId . '_tech') ?? 0;
		}

		if (!$techId) {
			return null;
		}

		if (Vars::getItemType($techId) != ItemType::TECH) {
			return null;
		}

		return $this->technologies->getByEntityId($techId);
	}

	public function setTech(int|string $techId, int $level): void
	{
		$entity = $this->getTech($techId);
		$entity->level = $level;

		$this->save();
	}

	public function getTechLevel(int|string $techId): int
	{
		return $this->getTech($techId)->level ?? 0;
	}
}
