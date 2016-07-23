<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

interface Mission
{
	public function TargetEvent();

	public function EndStayEvent();

	public function ReturnEvent();
}