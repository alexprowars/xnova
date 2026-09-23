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
		return __('fleet_engine.stay.subject');
	}

	public function render(): string
	{
		$message = __('fleet_engine.stay.returned', [
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
