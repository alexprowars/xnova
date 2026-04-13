<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionGainResourcesMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionGainResources';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		return __('fleet_engine.sys_expe_found_ress_' . $this->data['type']);
	}
}
