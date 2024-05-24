<?php

namespace App\Notifications;

use App\Mail\UserLostPasswordSuccess;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
	use Queueable;

	public function __construct(protected string $password)
	{
	}

	public function via(): array
	{
		return ['mail'];
	}

	public function toMail(User $notifiable): Mailable
	{
		return (new UserLostPasswordSuccess([
			'#EMAIL#' => $notifiable->getEmailForPasswordReset(),
			'#NAME#' => $notifiable->username,
			'#PASSWORD#' => $this->password,
		]))
		->to($notifiable->getEmailForPasswordReset());
	}
}
