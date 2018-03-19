<?php

namespace Xnova\Models\Planet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Database;
use Xnova\Vars;
use Phalcon\Exception;

/**
 * @method Database getWriteConnection
 */
trait Unit
{
	/** @var bool|array */
	private $units = false;

	private function _afterUpdateUnits ()
	{
		if ($this->units !== false)
		{
			foreach ($this->units as &$unit)
			{
				if ($unit['id'] == 0 && $unit['amount'] > 0)
				{
					$this->getWriteConnection()->insertAsDict(DB_PREFIX.'planets_units', [
						'planet_id' => $this->id,
						'unit_id' => $unit['type'],
						'amount' => $unit['amount'],
					]);

					$unit['id'] = $this->getWriteConnection()->lastInsertId();
				}
				elseif ($unit['id'] > 0 && $unit['amount'] != $unit['~amount'])
				{
					if ($unit['amount'] > 0)
					{
						$this->getWriteConnection()->updateAsDict(DB_PREFIX.'planets_units', [
							'amount' => $unit['amount']
						], ['conditions' => 'id = ?', 'bind' => [$unit['id']]]);
					}
					else
						$this->getWriteConnection()->delete(DB_PREFIX.'planets_units', 'id = ?', [$unit['id']]);
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

		$items = $this->getWriteConnection()->query('SELECT * FROM '.DB_PREFIX.'planets_units WHERE planet_id = ?', [$this->id]);

		while ($item = $items->fetch())
		{
			$this->units[$item['unit_id']] = [
				'id'		=> (int) $item['id'],
				'type'		=> (int) $item['unit_id'],
				'amount'	=> (int) $item['amount'],
				'~amount'	=> (int) $item['amount'],
				'power'		=> (int) $item['power'],
				'~power'	=> (int) $item['power']
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