<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class AllianceMemberAcceptMessage extends AbstractMessage
{
	protected string $type = 'AllianceMemberAccept';

	public function render(): string
	{
		return 'Привет!<br>Альянс <b>' . $this->data['name'] . '</b> принял вас в свои ряды!' . ((!empty($this->data['message'])) ? '<br>Приветствие:<br>' . $this->data['message'] : '');
	}
}
