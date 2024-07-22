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
			subject: config('app.name') . ': Уведомление об удалении аккаунта: ' . config('game.universe') . ' вселенная',
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
