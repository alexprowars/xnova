<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserLostPasswordSuccess extends Mailable
{
	public function __construct(protected User $user, protected string $password)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: config('app.name') . ': Новый пароль',
		);
	}

	public function content(): Content
	{
		return new Content('email.remind_2', with: [
			'user' => $this->user,
			'password' => $this->password,
		]);
	}
}
