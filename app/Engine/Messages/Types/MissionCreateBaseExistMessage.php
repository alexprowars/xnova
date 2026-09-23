<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionCreateBaseExistMessage extends AbstractMessage
{
	protected string $type = 'MissionCreateBaseExist';

	public function getSubject(): ?string
	{
		return __('fleet_engine.base.subject');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.base.occupied', $this->data);
	}
}
