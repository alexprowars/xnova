<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class AllianceMemberRejectMessage extends AbstractMessage
{
	protected string $type = 'AllianceMemberRequesReject';

	public function render(): string
	{
		return 'Привет!<br>Альянс <b>' . $this->data['name'] . '</b> отклонил вашу кандидатуру!' . ((!empty($this->data['message'])) ? '<br>Причина:<br>' . $this->data['message'] : '');
	}
}
