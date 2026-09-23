<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionGainResourcesMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionGainResources';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		return __('fleet_engine.expedition.resources.' . $this->data['type']);
	}
}
