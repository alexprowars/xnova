<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
	protected $table = 'chat';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'date' => 'immutable_datetime',
		'recipient_ids' => 'array',
	];

	/** @return array{users: array, private: bool} */
	public function getRecipients(): array
	{
		$message = $this->message;

		$publicUsers = [];
		$privateUsers = [];

		if (preg_match_all('/приватно \[(.*?)]/iu', $message, $match)) {
			$privateUsers = array_map('trim', $match[1]);
		}

		if (preg_match_all('/для \[(.*?)]/iu', $message, $match)) {
			$publicUsers = array_map('trim', $match[1]);

			if (!empty($privateUsers)) {
				$privateUsers = array_unique(array_merge($privateUsers, $publicUsers));

				$publicUsers = [];
			}
		}

		return [
			'users' => !empty($privateUsers) ? $privateUsers : $publicUsers,
			'private' => !empty($privateUsers),
		];
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
