<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Missions;

interface Mission
{
	public function targetEvent();

	public function endStayEvent();

	public function returnEvent();
}