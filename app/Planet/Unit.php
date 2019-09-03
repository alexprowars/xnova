<?php

namespace Xnova\Planet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Xnova\Exceptions\Exception;
use Xnova\Models\PlanetsUnits;
use Xnova\Vars;

trait Unit
{
	/** @var bool|array */
	private $units = false;

	public function _afterUpdateUnits ()
	{
		if ($this->units !== false)
		{
			foreach ($this->units as &$unit)
			{
				if ($unit['id'] == 0 && $unit['amount'] > 0)
				{
					$unit['id'] = DB::table('planets_units')->insertGetId([
						'planet_id' => $this->id,
						'unit_id' => $unit['type'],
						'amount' => $unit['amount']
					]);
				}
				elseif ($unit['id'] > 0 && $unit['amount'] != $unit['~amount'])
				{
					if ($unit['amount'] > 0)
					{
						DB::table('planets_units')
							->where('id', $unit['id'])
							->update(['amount' => $unit['amount']]);
					}
					else
						DB::table('planets_units')->where('id', $unit['id'])->delete();
				}

				$unit['~amount'] = $unit['amount'];
			}

			unset($unit);
		}
	}

	private function getUnitsData ()
	{
		if ($this->units !== false)
			return;

		$this->units = [];

		$items = PlanetsUnits::query()->where('planet_id', $this->id)->get();

		foreach ($items as $item)
		{
			$this->units[$item->unit_id] = [
				'id'		=> (int) $item->id,
				'type'		=> (int) $item->unit_id,
				'amount'	=> (int) $item->amount,
				'~amount'	=> (int) $item->amount,
				'power'		=> (int) $item->power,
				'~power'	=> (int) $item->power,
			];
		}
	}

	public function clearUnitsData ()
	{
		$this->units = false;
	}

	public function getUnit ($unitId)
	{
		if (!is_numeric($unitId))
			$unitId = Vars::getIdByName($unitId);

		$unitId = (int) $unitId;

		if (!$unitId)
			throw new Exception('getUnit not found');

		if (!$unitId)
			return false;

		if ($this->units === false)
			$this->getUnitsData();

		if (isset($this->units[$unitId]))
			return $this->units[$unitId];

		if (!in_array(Vars::getItemType($unitId), [Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]))
			return false;

		$this->units[$unitId] = [
			'id'		=> 0,
			'type'		=> $unitId,
			'amount'	=> 0,
			'~amount'	=> 0,
			'power'		=> false,
			'~power'	=> false
		];

		return $this->units[$unitId];
	}

	public function setUnit ($unitId, $count, $isDifferent = false)
	{
		$unit = $this->getUnit($unitId);

		if ($isDifferent)
			$this->units[$unit['type']]['amount'] = $unit['amount'] + (int) $count;
		else
			$this->units[$unit['type']]['amount'] = (int) $count;
	}

	public function getUnitCount ($unitId)
	{
		$unit = $this->getUnit($unitId);

		return $unit ? $unit['amount'] : 0;
	}
}