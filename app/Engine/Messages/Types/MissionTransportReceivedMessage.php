<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionTransportReceivedMessage extends AbstractMessage
{
	protected string $type = 'MissionTransportReceived';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_tower');
	}

	public function render(): string
	{
		$this->data['start'] = Coordinates::fromArray($this->data['start'])->getLink();
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.sys_tran_mess_user', $this->data);
	}
}
