<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionMissileAttackMessage extends AbstractMessage
{
	protected string $type = 'MissionMissileAttack';

	public function getSubject(): ?string
	{
		return 'Ракетная атака';
	}

	public function render(): string
	{
		if (empty($this->data)) {
			$message = 'Вражеская ракетная атака была отбита ракетами-перехватчиками<br>';
		} else {
			$message = 'Произведена межпланетная атака (' . $this->data['missiles'] . ' ракет) с ' . $this->data['planet']['name'] . ' ' . Coordinates::fromArray($this->data['planet'])->getLink() . ' ';
			$message .= 'на планету ' . $this->data['target']['name'] . ' ' . Coordinates::fromArray($this->data['target'])->getLink() . '.<br><br>';

			if ($this->data['missiles_destroyed'] > 0) {
				$message .= $this->data['missiles_destroyed'] . ' ракеты-перехватчика частично отбили атаку вражеских межпланетных ракет.<br>';
			}

			foreach ($this->data['destroyed'] as $id => $count) {
				$message .= __('main.tech.' . $id) . ' (' . $count . ' уничтожено)<br>';
			}

			if (empty($this->data['destroyed'])) {
				$message .= 'Нет обороны для разрушения!';
			}
		}

		return $message;
	}
}
