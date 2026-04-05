<?php

namespace App\Engine\Messages\Messages;

use App\Engine\Coordinates;
use App\Engine\Messages\MessageContract;
use App\Format;

class RecyclingMessage implements MessageContract
{
	public function format(array $message): string
	{
		$target = new Coordinates(
			$message['galaxy'],
			$message['system'],
			$message['planet'],
		)->getLink();

		return __('fleet_engine.sys_recy_gotten', [
			'm' => Format::number($message['metal']),
			'mt' => __('main.metal'),
			'c' => Format::number($message['crystal']),
			'ct' => __('main.crystal'),
			'target' => $target,
		]);
	}
}
