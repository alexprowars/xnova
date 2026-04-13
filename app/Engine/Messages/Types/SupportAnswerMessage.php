<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class SupportAnswerMessage extends AbstractMessage
{
	protected string $type = 'SupportAnswer';

	public function render(): string
	{
		return '<a href="/support/' . $this->data['ticket_id'] . '" target="_blank">Поступил ответ на тикет №' . $this->data['ticket_id'] . '</a>';
	}
}
