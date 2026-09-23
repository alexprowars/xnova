<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionDelayMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionDelay';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		if ($this->data['time'] == 'slow') {
			return __('fleet_engine.expedition.delay.slow.' . $this->data['type']);
		}

		return __('fleet_engine.expedition.delay.fast.' . $this->data['type']);
	}
}
