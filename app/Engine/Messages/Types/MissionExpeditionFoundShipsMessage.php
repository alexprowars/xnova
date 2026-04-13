<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionExpeditionFoundShipsMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionFoundShips';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		$result = match ($this->data['event_type']) {
			2 => __('fleet_engine.sys_expe_found_ships_3_' . $this->data['event_subtype']),
			1 => __('fleet_engine.sys_expe_found_ships_2_' . $this->data['event_subtype']),
			default => __('fleet_engine.sys_expe_found_ships_1_' . $this->data['event_subtype']),
		};

		foreach ($this->data['units'] as $id => $count) {
			$result  .= '<br>' . __('main.tech.' . $id) . ': ' . Format::number($count);
		}

		return $result;
	}
}
