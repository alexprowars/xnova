<?php

namespace App\Notifications;

use App\Engine\Enums\MessageType;
use App\Models\User;
use Illuminate\Notifications\Notification;

class MessageNotification extends Notification
{
	public function __construct(protected $sender, protected MessageType $type, protected $subject, protected $message)
	{
	}

	public function via()
	{
		return MessagesChannel::class;
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
