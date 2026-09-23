<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionExpeditionFoundShipsMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionFoundShips';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		$result = match ($this->data['type']) {
			2 => __('fleet_engine.expedition.ships.3.' . $this->data['event']),
			1 => __('fleet_engine.expedition.ships.2.' . $this->data['event']),
			default => __('fleet_engine.expedition.ships.1.' . $this->data['event']),
		};

		foreach ($this->data['units'] as $id => $count) {
			$result  .= '<br>' . __('main.tech.' . $id) . ': ' . Format::number($count);
		}

		return $result;
	}
}
