<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionFailedMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionLossFleet';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		return __('fleet_engine.sys_expe_nothing_' . $this->data['type']);
	}
}
