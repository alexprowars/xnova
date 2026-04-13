<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionDestructionFailureMessage extends AbstractMessage
{
	protected string $type = 'MissionDestructionFailure';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_destruc_report');
	}

	public function render(): string
	{
		return __('fleet_engine.sys_destruc_stop');
	}
}
