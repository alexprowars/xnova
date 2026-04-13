<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class AcsFleetArrivedMessage extends AbstractMessage
{
	protected string $type = 'AcsFleetArrived';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_tower');
	}

	public function render(): string
	{
		$this->data['start'] = Coordinates::fromArray($this->data['start'])->getLink();
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.sys_stay_mess_user', $this->data);
	}
}
