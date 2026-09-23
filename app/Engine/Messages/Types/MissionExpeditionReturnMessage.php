<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionExpeditionReturnMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionReturn';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		return __('fleet_engine.expedition.returned', [
			'metal' => Format::number($this->data['metal']),
			'crystal' => Format::number($this->data['crystal']),
			'deuterium' => Format::number($this->data['deuterium']),
		]);
	}
}
