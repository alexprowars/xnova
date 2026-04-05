<?php

namespace App\Engine\Messages\Messages;

use App\Engine\Messages\MessageContract;

class TextMessage implements MessageContract
{
	public function format(array $message): string
	{
		if (!empty($message['text'])) {
			$message['text'] = __($message['text']);
		}

		return str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes($message['text'] ?? ''));
	}
}
