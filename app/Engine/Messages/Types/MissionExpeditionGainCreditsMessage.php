<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;
use App\Format;

class MissionExpeditionGainCreditsMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionGainCredits';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_expe_report');
	}

	public function render(): string
	{
		$result = __('fleet_engine.sys_expe_found_dm_' . $this->data['type']);

		if (isset($this->data['credits'])) {
			$result .= '<br>' . __('fleet_engine.sys_expe_credits_received', [
				'credits' => Format::number($this->data['credits']),
			]);
		}

		return $result;
	}
}
