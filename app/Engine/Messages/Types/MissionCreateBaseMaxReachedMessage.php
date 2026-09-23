<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionCreateBaseMaxReachedMessage extends AbstractMessage
{
	protected string $type = 'MissionCreateBaseMaxReached';

	public function getSubject(): ?string
	{
		return __('fleet_engine.base.subject');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.base.limit_reached', $this->data);
	}
}
