<?php

namespace App\Notifications;

use App\Engine\Enums\MessageType;
use App\Engine\Messages\MessageContract;
use App\Models\User;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification
{
	public function __construct(protected User|int|null $sender, protected MessageType $type, protected ?string $subject, protected array|string|MessageContract $message)
	{
	}

	public function via(): array
	{
		return [MessagesChannel::class];
	}

	public function toMessages(User $notifiable): array
	{
		return [
			$notifiable,
			$this->sender,
			$this->type,
			$this->subject,
			$this->message,
		];
	}
}
