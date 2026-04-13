<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;
use App\Format;
use Illuminate\Support\Facades\Crypt;

class MissionExpeditionAttackMessage extends AbstractMessage
{
	protected string $type = 'MissionExpeditionAttack';

	public function getSubject(): ?string
	{
		return __('fleet_engine.sys_mess_tower');
	}

	public function render(): string
	{
		return sprintf(
			'<a href="%s" target="_blank"><center><span style="color: %s">%s %s</span></a><br><br><span style="color: %s">%s: %s</span> <span style="color: %s">%s: %s</span><br>%s %s:<span style="color: #adaead">%s</span> %s:<span style="color: #ef51ef">%s</span> %s:<span style="color: #f77542">%s</span><br>%s %s:<span style="color: #adaead">%s</span> %s:<span style="color: #ef51ef">%s</span><br></center>',
			'/rw/' . $this->data['report_id'] . '?signature=' . Crypt::encrypt($this->data['report_id']),
			$this->data['color_att'],
			'Боевой доклад',
			Coordinates::fromArray($this->data),
			$this->data['color_att'],
			__('fleet_engine.sys_perte_attaquant'),
			Format::number($this->data['lost']['att']),
			$this->data['color_def'],
			__('fleet_engine.sys_perte_defenseur'),
			Format::number($this->data['lost']['def']),
			__('fleet_engine.sys_gain'),
			__('main.metal'),
			0,
			__('main.crystal'),
			0,
			__('main.deuterium'),
			0,
			__('fleet_engine.sys_debris'),
			__('main.metal'),
			0,
			__('main.crystal'),
			0
		);
	}
}
