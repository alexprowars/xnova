<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserRegistration extends Mailable
{
	public function __construct(protected User $user, protected string $password)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: __('main.mail.user_registration_subject', ['app' => config('app.name')]),
		);
	}

	public function content(): Content
	{
		return new Content('email.registration', with: [
			'user' => $this->user,
			'password' => $this->password,
		]);
	}
}
