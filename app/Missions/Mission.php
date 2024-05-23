<?php

namespace App\Missions;

interface Mission
{
	public function targetEvent();

	public function endStayEvent();

	public function returnEvent();
}