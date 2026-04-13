<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionStayReturnMessage extends AbstractMessage
{
	protected string $type = 'MissionStayReturn';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_qg');
	}

	public function render(): string
	{
		$message = __('fleet_engine.sys_stay_mess_back', [
			'target' => Coordinates::fromArray($this->data)->getLink(),
			'metal' => Format::number($this->data['metal']),
			'crystal' => Format::number($this->data['crystal']),
			'deuterium' => Format::number($this->data['deuterium']),
		]);

		foreach ($this->data['units'] as $id => $count) {
			$message .= ', ' . __('main.tech.' . $id) . ': ' . $count;
		}

		return $message;
	}
}
