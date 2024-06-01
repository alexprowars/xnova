<?php

namespace App\Engine\Fleet\Missions;

interface Mission
{
	public function targetEvent();

	public function endStayEvent();

	public function returnEvent();
}