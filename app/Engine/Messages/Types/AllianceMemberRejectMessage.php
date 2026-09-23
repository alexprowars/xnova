<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class AllianceMemberRejectMessage extends AbstractMessage
{
	protected string $type = 'AllianceMemberRequesReject';

	public function render(): string
	{
		return __('messages.alliance_member_reject', ['name' => $this->data['name']])
			. (!empty($this->data['message']) ? __('messages.alliance_member_reject_reason', ['message' => $this->data['message']]) : '');
	}
}
