<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UserDelete extends Mailable
{
	public function __construct(protected User $user)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: __('main.mail.user_delete_subject', ['app' => config('app.name'), 'universe' => config('game.universe')]),
		);
	}

	public function content(): Content
	{
		return new Content('email.delete', with: [
			'user' => $this->user,
			'time' => config('game.deleteTime', 7),
		]);
	}
}
