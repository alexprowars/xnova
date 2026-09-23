<?php

namespace App\Engine\Messages\Types;

use App\Engine\Coordinates;
use App\Engine\Messages\AbstractMessage;

class AcsRequestMessage extends AbstractMessage
{
	protected string $type = 'AcsRequest';

	public function getSubject(): ?string
	{
		return __('messages.acs_request_subject');
	}

	public function render(): string
	{
		return __('messages.acs_request', [
			'user' => $this->data['user'],
			'planet' => $this->data['planet']['name'],
			'coordinates' => Coordinates::fromArray($this->data['planet'])->getLink(),
			'owner' => $this->data['planet']['user'],
			'assault' => $this->data['assault'],
		]);
	}
}
