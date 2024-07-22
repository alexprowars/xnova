<?php

namespace App\Notifications;

use App\Mail\UserDelete;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class UserDeleteNotification extends Notification
{
	use Queueable;

	public function via(): array
	{
		return ['mail'];
	}

	public function toMail(User $notifiable): Mailable
	{
		return (new UserDelete($notifiable))
			->to($notifiable->email);
	}
}
