<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class FriendsRequestMessage extends AbstractMessage
{
	protected string $type = 'FriendsRequest';

	public function getSubject(): ?string
	{
		return __('messages.friends_request_subject');
	}

	public function render(): string
	{
		return __('messages.friends_request', ['name' => $this->data['name']]);
	}
}
