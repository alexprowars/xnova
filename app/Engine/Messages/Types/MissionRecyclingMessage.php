<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionRecyclingMessage extends AbstractMessage
{
	protected string $type = 'MissionRecycling';

	public function getSubject(): ?string
	{
		return __('fleet_engine.recycling.subject');
	}

	public function render(): string
	{
		return __('fleet_engine.recycling.collected', [
			'metal' => Format::number($this->data['metal']),
			'crystal' => Format::number($this->data['crystal']),
			'target' => Coordinates::fromArray($this->data['target'])->getLink(),
		]);
	}
}
