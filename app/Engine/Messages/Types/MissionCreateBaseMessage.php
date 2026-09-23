<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionCreateBaseMessage extends AbstractMessage
{
	protected string $type = 'MissionCreateBase';

	public function getSubject(): ?string
	{
		return __('fleet_engine.base.subject');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.base.success', $this->data);
	}
}
