<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionColonizationMaxReachedMessage extends AbstractMessage
{
	protected string $type = 'MissionColonizationMaxReached';

	public function getSubject(): ?string
	{
		return __('fleet_engine.colonization.subject');
	}

	public function render(): string
	{
		$this->data['target'] = Coordinates::fromArray($this->data['target'])->getLink();

		return __('fleet_engine.colonization.limit_reached', $this->data);
	}
}
