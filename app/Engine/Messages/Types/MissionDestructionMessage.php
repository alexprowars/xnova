<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionDestructionMessage extends AbstractMessage
{
	protected string $type = 'MissionDestruction';

	public function getSubject(): ?string
	{
		return __('fleet_engine.destruction.report');
	}

	public function render(): string
	{
		if ($this->data['destroyed']) {
			if (!$this->data['killed']) {
				return __('fleet_engine.destruction.moon_destroyed');
			} else {
				return __('fleet_engine.destruction.both_destroyed');
			}
		} elseif (!$this->data['killed']) {
			return __('fleet_engine.destruction.failed');
		} else {
			return __('fleet_engine.destruction.fleet_destroyed');
		}
	}
}
