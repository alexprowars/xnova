<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class MissionDestructionMessage extends AbstractMessage
{
	protected string $type = 'MissionDestruction';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_destruc_report');
	}

	public function render(): string
	{
		if ($this->data['destroyed']) {
			if (!$this->data['killed']) {
				return __('fleet_engine.sys_destruc_reussi');
			} else {
				return __('fleet_engine.sys_destruc_all');
			}
		} elseif (!$this->data['killed']) {
			return __('fleet_engine.sys_destruc_null');
		} else {
			return __('fleet_engine.sys_destruc_echec');
		}
	}
}
