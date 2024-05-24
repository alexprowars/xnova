<?php

namespace App\Notifications;

use App\Mail\UserRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class UserRegistrationNotification extends Notification
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
		return (new UserRegistration([
			'#EMAIL#' => $notifiable->email,
			'#PASSWORD#' => $this->password,
		]))
		->to($notifiable->email);
	}
}
