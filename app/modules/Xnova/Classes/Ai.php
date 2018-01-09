<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Models\Planet;
use Xnova\Models\User;

class Ai
{
	private $_playerId = 0;
	/**
	 * @var bool|User
	 */
	private $_playerData = false;
	private $_log = [];

	public function __construct ($playerId)
	{
		$this->_playerId = (int) $playerId;

		$this->addLog('///// BOT UID: '.$this->_playerId.' STARTED '.date("d.m.Y H:i:s").'');
	}

	private function getPlayerId ()
	{
		return $this->_playerId;
	}

	public function getLog ()
	{
		echo "\n";
		echo implode("\n", $this->_log);
		echo "\n\n";
	}

	public function addLog ($message)
	{
		$this->_log[] = $message;
	}

	public function update ()
	{
		$this->_playerData = User::findFirst($this->getPlayerId());

		$planets = $this->_playerData->getUserPlanets($this->_playerData->getId());

		foreach ($planets as $row)
		{
			$planet = Planet::findFirst($row['id']);
			$planet->assignUser($this->_playerData);
			$planet->checkUsedFields();
			$planet->resourceUpdate();
			$planet->updateQueueList();

			$this->addLog('planet uid: '.$planet->id.' name: "'.$planet->name.'" updated');

			if ($planet->field_current < $planet->getMaxFields())
			{
				$this->addLog('try to build stores');
			}
		}
	}
}