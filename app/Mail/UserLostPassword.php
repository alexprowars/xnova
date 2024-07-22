<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserLostPassword extends Mailable
{
	public function __construct(protected User $user, protected string $token)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: config('app.name') . ': Восстановление забытого пароля',
		);
	}

	public function content(): Content
	{
		return new Content('email.remind_1', with: [
			'user' => $this->user,
			'link' => route('password.reset', ['token' => $this->token, 'email' => $this->user->getEmailForPasswordReset()]),
		]);
	}
}
