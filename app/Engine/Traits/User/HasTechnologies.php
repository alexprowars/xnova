<?php

namespace App\Engine\Traits\User;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;

trait HasTechnologies
{
	public function getTech($techId)
	{
		if (!is_numeric($techId)) {
			$techId = Vars::getIdByName($techId . '_tech');
		}

		if (!$techId) {
			return null;
		}

		if (Vars::getItemType($techId) != ItemType::TECH) {
			return null;
		}

		return $this->technologies->getByEntityId($techId);
	}

	public function setTech($techId, int $level)
	{
		$entity = $this->getTech($techId);
		$entity->level = $level;

		$this->save();
	}

	public function getTechLevel($techId)
	{
		return $this->getTech($techId)->level ?? 0;
	}
}
