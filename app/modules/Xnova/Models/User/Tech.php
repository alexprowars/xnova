<?php

namespace Xnova\Models\User;

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
trait Tech
{
	/** @var bool|array */
	private $technology = false;

	private function _afterUpdateTechs ()
	{
		if ($this->technology !== false)
		{
			foreach ($this->technology as &$tech)
			{
				if ($tech['id'] == 0 && $tech['level'] > 0)
				{
					$this->getWriteConnection()->insertAsDict(DB_PREFIX.'users_tech', [
						'user_id' => $this->id,
						'tech_id' => $tech['type'],
						'level' => $tech['level'],
					]);

					$tech['id'] = $this->getWriteConnection()->lastInsertId();
				}
				elseif ($tech['id'] > 0 && $tech['level'] != $tech['~level'])
				{
					if ($tech['level'] - $tech['~level'] > 1)
					{
						file_put_contents(ROOT_PATH.'/php_errors.log', "\n\n".print_r($_SERVER, true)."\n\n".print_r($_REQUEST, true)."\n\n".print_r($this->technology, true)."\n\n", FILE_APPEND);

					}

					if ($tech['level'] > 0)
					{
						$this->getWriteConnection()->updateAsDict(DB_PREFIX.'users_tech', [
							'level' => $tech['level']
						], ['conditions' => 'id = ?', 'bind' => [$tech['id']]]);
					}
					else
						$this->getWriteConnection()->delete(DB_PREFIX.'users_tech', 'id = ?', [$tech['id']]);
				}

				$tech['~level'] = $tech['level'];
			}

			unset($tech);
		}
	}

	private function getTechnologyData ()
	{
		if ($this->technology !== false)
			return;

		$this->technology = [];

		$items = $this->getWriteConnection()->query('SELECT * FROM '.DB_PREFIX.'users_tech WHERE user_id = ?', [$this->id]);

		while ($item = $items->fetch())
		{
			$this->technology[$item['tech_id']] = [
				'id'		=> (int) $item['id'],
				'type'		=> (int) $item['tech_id'],
				'level'		=> (int) $item['level'],
				'~level'	=> (int) $item['level']
			];
		}
	}

	public function getTech ($techId)
	{
		$_techId = $techId;

		if (!is_numeric($techId))
			$techId = Vars::getIdByName($techId.'_tech');

		if (!$techId)
			throw new Exception('getTech::'.$_techId.' not found');

		$techId = (int) $techId;

		if (!$techId)
			return false;

		if ($this->technology === false)
			$this->getTechnologyData();

		if (isset($this->technology[$techId]))
			return $this->technology[$techId];

		if (Vars::getItemType($techId) != Vars::ITEM_TYPE_TECH)
			return false;

		$this->technology[$techId] = [
			'id'		=> 0,
			'type'		=> $techId,
			'level'		=> 0,
			'~level'	=> 0
		];

		return $this->technology[$techId];
	}

	public function setTech ($techId, $level)
	{
		$tech = $this->getTech($techId);

		$this->technology[$tech['type']]['level'] = (int) $level;
	}

	public function getTechLevel ($techId)
	{
		$tech = $this->getTech($techId);

		return $tech ? $tech['level'] : 0;
	}
}