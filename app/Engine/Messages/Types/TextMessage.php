<?php

namespace App\Engine\Messages\Types;

use App\Engine\Messages\AbstractMessage;

class TextMessage extends AbstractMessage
{
	protected string $type = 'Text';

	public function render(): string
	{
		$message = $this->data['text'] ?? '';

		if (!empty($message)) {
			$message = __($message);
		}

		return str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes($message));
	}
}
