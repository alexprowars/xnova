<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionBattleMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionBattle';

	public function getSubject(): ?string
	{
		return __('fleet_engine.expedition.report');
	}

	public function render(): string
	{
		return __('fleet_engine.expedition.attack.' . $this->data['which'] . '.' . $this->data['type']);
	}
}
