<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionStayMessage extends AbstractMessage
{
	protected string $type = 'MissionStay';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_qg');
	}

	public function render(): string
	{
		$message = __('fleet_engine.sys_stat_mess', [
			'target' => Coordinates::fromArray($this->data)->getLink(),
			'metal' => Format::number($this->data['metal']),
			'crystal' => Format::number($this->data['crystal']),
			'deuterium' => Format::number($this->data['deuterium']),
		]);

		$addUnits = '';

		foreach ($this->data['units'] as $id => $count) {
			$addUnits .= ', ' . __('main.tech.' . $id) . ': ' . $count;
		}

		if (!empty($addUnits)) {
			$message .= '<br>' . trim(substr($addUnits, 1));
		}

		return $message;
	}
}
