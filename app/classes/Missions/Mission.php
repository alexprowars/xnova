<?php

namespace App\Missions;

interface Mission
{
	public function TargetEvent();

	public function EndStayEvent();

	public function ReturnEvent();
}
 
?>