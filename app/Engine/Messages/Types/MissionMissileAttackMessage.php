<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class MissionMissileAttackMessage extends AbstractMessage
{
	protected string $type = 'MissionMissileAttack';

	public function getSubject(): ?string
	{
		return __('fleet_engine.missile.subject');
	}

	public function render(): string
	{
		if (empty($this->data)) {
			$message = __('fleet_engine.missile.intercepted');
		} else {
			$message = __('fleet_engine.missile.launched', [
				'count' => $this->data['missiles'],
				'origin_name' => $this->data['planet']['name'],
				'origin' => Coordinates::fromArray($this->data['planet'])->getLink(),
				'target_name' => $this->data['target']['name'],
				'target' => Coordinates::fromArray($this->data['target'])->getLink(),
			]);

			if ($this->data['missiles_destroyed'] > 0) {
				$message .= __('fleet_engine.missile.partially_intercepted', ['count' => $this->data['missiles_destroyed']]);
			}

			foreach ($this->data['destroyed'] as $id => $count) {
				$message .= __('fleet_engine.missile.destroyed', [
					'name' => __('main.tech.' . $id),
					'count' => $count,
				]);
			}

			if (empty($this->data['destroyed'])) {
				$message .= __('fleet_engine.missile.no_defense');
			}
		}

		return $message;
	}
}
