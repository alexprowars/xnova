<?php

namespace App\Engine\Traits\User;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Models\UserTech;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTechnologies
{
	/** @return HasMany<UserTech, $this> */
	public function techs(): HasMany
	{
		return $this->hasMany(UserTech::class);
	}

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

		$entity = $this->techs->firstWhere('tech_id', $techId);

		if (!$entity) {
			$entity = new UserTech(['tech_id' => $techId]);

			$this->techs->add($entity);
		}

		$entity->user()->associate($this);

		return $entity;
	}

	public function setTech($techId, int $level)
	{
		$entity = $this->getTech($techId);

		$entity->level = $level;
		$entity->save();
	}

	public function getTechLevel($techId)
	{
		return $this->getTech($techId)->level ?? 0;
	}
}
