<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class AllianceMemberAcceptMessage extends AbstractMessage
{
	protected string $type = 'AllianceMemberAccept';

	public function render(): string
	{
		return __('messages.alliance_member_accept', ['name' => $this->data['name']])
			. (!empty($this->data['message']) ? __('messages.alliance_member_greeting', ['message' => $this->data['message']]) : '');
	}
}
