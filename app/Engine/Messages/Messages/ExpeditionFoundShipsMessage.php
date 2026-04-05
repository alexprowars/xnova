<?php

namespace App\Engine\Messages\Messages;

use App\Engine\Messages\MessageContract;
use App\Format;

class ExpeditionFoundShipsMessage implements MessageContract
{
	public function format(array $message): string
	{
		$result = match ($message['event_type']) {
			2 => __('fleet_engine.sys_expe_found_ships_3_' . $message['event_subtype']),
			1 => __('fleet_engine.sys_expe_found_ships_2_' . $message['event_subtype']),
			default => __('fleet_engine.sys_expe_found_ships_1_' . $message['event_subtype']),
		};

		foreach ($message['units'] as $id => $count) {
			$result  .= '<br>' . __('main.tech.' . $id) . ': ' . Format::number($count);
		}

		return $result;
	}
}
