<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionCreateBaseErrorMessage extends AbstractMessage
{
	protected string $type = 'MissionCreateBaseError';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_base_mess_from');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.sys_base_badpos', $this->data);
	}
}
