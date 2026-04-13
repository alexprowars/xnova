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
		return 'Боевой доклад';
	}

	public function render(): string
	{
		$result  = '<div class="text-center">';
		$result .= '<a href="/rw/' . $this->data['report_id'] . '?signature=' . Crypt::encrypt($this->data['report_id']) . '" target="_blank">';
		$result .= '<span style="color:' . ($this->data['color'] ?? 'orange') . '">' . __('fleet_engine.sys_mess_attack_report') . ' [' . $this->data['galaxy'] . ":" . $this->data['system'] . ':' . $this->data['planet'] . ']</span></a>';
		$result .= '</div>';

		if (isset($this->data['lost'])) {
			$result .= '<div class="text-center mt-4">';
			$result .= '<div class="negative">' . __('fleet_engine.sys_perte_attaquant') . ': ' . Format::number($this->data['lost']['att']) . '</span><span class="positive">   ' . __('fleet_engine.sys_perte_defenseur') . ': ' . Format::number($this->data['lost']['def']) . '</div>';
			$result .= '<div>' . __('fleet_engine.sys_gain') . ' м: <span style="color:#adaead">' . Format::number($this->data['steal']['metal']) . '</span>, к: <span style="color:#ef51ef">' . Format::number($this->data['steal']['crystal']) . '</span>, д: <span style="color:#f77542">' . Format::number($this->data['steal']['deuterium']) . '</span></div>';
			$result .= '<div>' . __('fleet_engine.sys_debris') . ' м: <span style="color:#adaead">' . Format::number($this->data['debris']['metal']) . '</span>, к: <span style="color:#ef51ef">' . Format::number($this->data['debris']['crystal']) . '</span></div>';
			$result .= '</div>';
		}

		return $result;
	}
}
