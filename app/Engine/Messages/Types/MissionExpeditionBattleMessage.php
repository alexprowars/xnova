<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionExpeditionBattleMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionBattle';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		return __('fleet_engine.sys_expe_attack_' . $this->data['which'] . '_' . $this->data['type']);
	}
}
