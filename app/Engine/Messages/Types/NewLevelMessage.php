<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class NewLevelMessage extends AbstractMessage
{
	protected string $type = 'NewLevel';

	public function render(): string
	{
		if ($this->data['type'] == 'mine') {
			return __('messages.new_industrial_level');
		}

		if ($this->data['type'] == 'raid') {
			return __('messages.new_military_level');
		}

		return '';
	}
}
