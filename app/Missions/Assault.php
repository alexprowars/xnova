<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;

class Assault extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->ReturnFleet();
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}