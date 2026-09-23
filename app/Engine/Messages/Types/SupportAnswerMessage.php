<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class SupportAnswerMessage extends AbstractMessage
{
	protected string $type = 'SupportAnswer';

	public function render(): string
	{
		return __('messages.support_answer', ['ticket_id' => $this->data['ticket_id']]);
	}
}
