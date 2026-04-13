<?php

namespace App\Notifications;

use App\Engine\Enums\MessageType;
use App\Engine\Messages\MessageContract;

class SystemMessage extends MessageNotification
{
	public function __construct(protected MessageType $type, protected array|string|MessageContract $message, protected ?string $subject = null)
	{
		parent::__construct(null, $type, $subject, $message);
	}
}
