<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionTransportArrivedMessage extends AbstractMessage
{
	protected string $type = 'MissionTransportArrived';

	public function getSubject(): ?string
	{
		return __('fleet_engine.transport.subject');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.transport.arrived', $this->data);
	}
}
