<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionEspionageNotifyMessage extends AbstractMessage
{
	protected string $type = 'MissionEspionageNotify';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_spy_activity');
	}

	public function render(): string
	{
		return __('fleet_engine.sys_mess_spy_ennemy', $this->data);
	}
}
