<?php

namespace App\Notifications;

use App\Engine\Enums\MessageType;
use App\Models\Message;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MessagesChannel
{
	public function send(object $notifiable, Notification $notification): void
	{
		/**
		 * @var User $user
		 * @var User|int|null $sender
		 * @var MessageType $type
		 * @var string $subject
		 * @var string $message
		 */
		[$user, $sender, $type, $subject, $message] = $notification->toMessages($notifiable);

		$authUser = Auth::user();

		if (!$sender && $authUser) {
			$sender = $authUser->id;
		}

		if ($sender instanceof User) {
			$sender = $sender->id;
		}

		$obj = new Message();
		$obj->user_id = $user->id;
		$obj->from_id = $sender ?: null;
		$obj->time = now();
		$obj->type = $type;
		$obj->theme = $subject;
		$obj->text = $message;

		if ($obj->save()) {
			if ($authUser && $user->id == $authUser->id) {
				$authUser->increment('messages');
			} else {
				$user->increment('messages');
			}
		}
	}
}
