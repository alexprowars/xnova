<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionFailedMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionFailed';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		return __('fleet_engine.expedition.empty.' . $this->data['type']);
	}
}
