<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionDelayMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionGainCredits';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		if ($this->data['time'] == 'slow') {
			return __('fleet_engine.sys_expe_time_slow_' . $this->data['type']);
		}

		return __('fleet_engine.sys_expe_time_fast_' . $this->data['type']);
	}
}
