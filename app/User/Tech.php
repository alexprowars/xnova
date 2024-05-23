<?php

namespace App\User;

use App\Exceptions\Exception;
use App\Models\UserTech;
use App\Vars;

trait Tech
{
	/** @var bool|array */
	private $technology = false;

	public function _afterUpdateTechs()
	{
		if ($this->technology !== false) {
			foreach ($this->technology as &$tech) {
				if ($tech['id'] == 0 && $tech['level'] > 0) {
					$tech['id'] = UserTech::query()->insertGetId([
						'user_id' => $this->id,
						'tech_id' => $tech['type'],
						'level' => $tech['level']
					]);
				} elseif ($tech['id'] > 0 && $tech['level'] != $tech['~level']) {
					if ($tech['level'] > 0) {
						UserTech::query()
							->where('id', $tech['id'])
							->update(['level' => $tech['level']]);
					} else {
						UserTech::query()->where('id', $tech['id'])->delete();
					}
				}

				$tech['~level'] = $tech['level'];
			}

			unset($tech);
		}
	}

	private function getTechnologyData()
	{
		if ($this->technology !== false) {
			return;
		}

		$this->technology = [];

		$items = UserTech::query()
			->where('user_id', $this->id)
			->get();

		foreach ($items as $item) {
			$this->technology[$item->tech_id] = [
				'id'		=> (int) $item->id,
				'type'		=> (int) $item->tech_id,
				'level'		=> (int) $item->level,
				'~level'	=> (int) $item->level,
			];
		}
	}

	public function getTech($techId)
	{
		$_techId = $techId;

		if (!is_numeric($techId)) {
			$techId = Vars::getIdByName($techId . '_tech');
		}

		if (!$techId) {
			throw new Exception('getTech::' . $_techId . ' not found');
		}

		$techId = (int) $techId;

		if (!$techId) {
			return false;
		}

		if ($this->technology === false) {
			$this->getTechnologyData();
		}

		if (isset($this->technology[$techId])) {
			return $this->technology[$techId];
		}

		if (Vars::getItemType($techId) != Vars::ITEM_TYPE_TECH) {
			return false;
		}

		$this->technology[$techId] = [
			'id'		=> 0,
			'type'		=> $techId,
			'level'		=> 0,
			'~level'	=> 0
		];

		return $this->technology[$techId];
	}

	public function setTech($techId, $level)
	{
		$tech = $this->getTech($techId);

		$this->technology[$tech['type']]['level'] = (int) $level;
	}

	public function getTechLevel($techId)
	{
		$tech = $this->getTech($techId);

		return $tech ? $tech['level'] : 0;
	}
}
