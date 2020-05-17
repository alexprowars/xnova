<?php

namespace Xnova\Planet;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Xnova\Exceptions\Exception;
use Xnova\Models\PlanetBuilding;
use Xnova\Vars;

trait Build
{
	/** @var bool|array */
	private $buildings = false;

	public function _afterUpdateBuildings()
	{
		if ($this->buildings !== false) {
			foreach ($this->buildings as &$building) {
				if ($building['id'] == 0 && $building['level'] > 0) {
					$building['id'] = DB::table('planets_buildings')->insertGetId([
						'planet_id' => $this->id,
						'build_id' => $building['type'],
						'level' => $building['level']
					]);
				} elseif ($building['id'] > 0 && $building['level'] != $building['~level']) {
					if ($building['level'] > 0) {
						DB::table('planets_buildings')
							->where('id', $building['id'])
							->update(['level' => $building['level']]);
					} else {
						DB::table('planets_buildings')->where('id', $building['id'])->delete();
					}
				}

				$building['~level'] = $building['level'];
			}

			unset($building);
		}
	}

	private function getBuildingsData()
	{
		if ($this->buildings !== false) {
			return;
		}

		$this->buildings = [];

		$items = PlanetBuilding::query()->where('planet_id', $this->id)->get();

		foreach ($items as $item) {
			$this->buildings[$item->build_id] = [
				'id'		=> (int) $item->id,
				'type'		=> (int) $item->build_id,
				'level'		=> (int) $item->level,
				'~level'	=> (int) $item->level,
				'power'		=> (int) $item->power,
				'~power'	=> (int) $item->power,
			];
		}
	}

	public function clearBuildingsData()
	{
		$this->buildings = false;
	}

	public function getBuild($buildId)
	{
		if (!is_numeric($buildId)) {
			$buildId = Vars::getIdByName($buildId);
		}

		$buildId = (int) $buildId;

		if (!$buildId) {
			throw new Exception('getBuild not found');
		}

		if (!$buildId) {
			return false;
		}

		if ($this->buildings === false) {
			$this->getBuildingsData();
		}

		if (isset($this->buildings[$buildId])) {
			return $this->buildings[$buildId];
		}

		if (Vars::getItemType($buildId) != Vars::ITEM_TYPE_BUILING) {
			return false;
		}

		$this->buildings[$buildId] = [
			'id'		=> 0,
			'type'		=> $buildId,
			'level'		=> 0,
			'~level'	=> 0,
			'power'		=> false,
			'~power'	=> false
		];

		return $this->buildings[$buildId];
	}

	public function setBuild($buildId, $level)
	{
		$build = $this->getBuild($buildId);

		$this->buildings[$build['type']]['level'] = max(0, (int) $level);
	}

	public function getBuildLevel($buildId)
	{
		$build = $this->getBuild($buildId);

		return $build ? $build['level'] : 0;
	}
}
