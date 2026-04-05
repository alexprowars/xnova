<?php

namespace App\Engine\Messages;

use App\Engine\Messages\Messages\AttackMessage;
use App\Engine\Messages\Messages\ExpeditionFoundShipsMessage;
use App\Engine\Messages\Messages\ExpeditionReturnMessage;
use App\Engine\Messages\Messages\RecyclingMessage;
use App\Engine\Messages\Messages\SpyMessage;
use App\Engine\Messages\Messages\TextMessage;
use App\Exceptions\Exception;

class MessageFormatter
{
	protected static array $messages = [
		'TextMessage' => TextMessage::class,
		'SpyMessage' => SpyMessage::class,
		'AttackMessage' => AttackMessage::class,
		'RecyclingMessage' => RecyclingMessage::class,
		'ExpeditionFoundShipsMessage' => ExpeditionFoundShipsMessage::class,
		'ExpeditionReturnMessage' => ExpeditionReturnMessage::class,
	];

	public static function format(array $messages): string
	{
		if (empty($messages['type']) || !isset(self::$messages[$messages['type']])) {
			throw new Exception('Invalid type');
		}

		$formatter = self::$messages[$messages['type']];

		return new $formatter()->format($messages);
	}
}
