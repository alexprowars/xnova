<?php

namespace App\Engine\Messages\Messages;

use App\Engine\Messages\MessageContract;
use App\Format;

class ExpeditionReturnMessage implements MessageContract
{
	public function format(array $message): string
	{
		return __('fleet_engine.sys_expe_back_home', [
			'mt' => __('main.metal'),
			'm' => Format::number($message['metal']),
			'ct' => __('main.crystal'),
			'c' => Format::number($message['crystal']),
			'dt' => __('main.deuterium'),
			'd' => Format::number($message['deuterium']),
		]);
	}
}
