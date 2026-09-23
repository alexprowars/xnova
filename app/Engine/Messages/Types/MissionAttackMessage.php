<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;
use App\Format;
use Illuminate\Support\Facades\Crypt;

class MissionAttackMessage extends AbstractMessage
{
	protected string $type = 'MissionAttack';

	public function getSubject(): ?string
	{
		return __('fleet_engine.battle.report');
	}

	public function render(): string
	{
		$result  = '<div class="text-center">';
		$result .= '<a href="/rw/' . $this->data['report_id'] . '?signature=' . Crypt::encrypt($this->data['report_id']) . '" target="_blank">';
		$result .= '<span style="color:' . ($this->data['color'] ?? 'orange') . '">' . __('fleet_engine.battle.report') . ' [' . $this->data['galaxy'] . ":" . $this->data['system'] . ':' . $this->data['planet'] . ']</span></a>';
		$result .= '</div>';

		if (isset($this->data['lost'])) {
			$result .= '<div class="text-center mt-4">';
			$result .= '<div class="negative">' . __('fleet_engine.battle.attacker_lost') . ': ' . Format::number($this->data['lost']['attackers']) . '</span><span class="positive">   ' . __('fleet_engine.battle.defender_lost') . ': ' . Format::number($this->data['lost']['defenders']) . '</div>';
			$result .= '<div>' . __('fleet_engine.battle.loot') . ' ' . __('fleet_engine.battle.metal_short') . ': <span style="color:#adaead">' . Format::number($this->data['steal']['metal']) . '</span>, ' . __('fleet_engine.battle.crystal_short') . ': <span style="color:#ef51ef">' . Format::number($this->data['steal']['crystal']) . '</span>, ' . __('fleet_engine.battle.deuterium_short') . ': <span style="color:#f77542">' . Format::number($this->data['steal']['deuterium']) . '</span></div>';
			$result .= '<div>' . __('fleet_engine.battle.debris') . ' ' . __('fleet_engine.battle.metal_short') . ': <span style="color:#adaead">' . Format::number($this->data['debris']['metal']) . '</span>, ' . __('fleet_engine.battle.crystal_short') . ': <span style="color:#ef51ef">' . Format::number($this->data['debris']['crystal']) . '</span></div>';
			$result .= '</div>';
		}

		return $result;
	}
}
