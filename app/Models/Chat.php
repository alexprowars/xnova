<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
	protected $guarded = [];
	private $isAllowed = false;

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function parse()
	{
		$message = $this->message;

		$publicUsers = [];
		$privateUsers = [];

		preg_match_all('/приватно \[(.*?)]/iu', $message, $match);

		if (isset($match[1])) {
			$privateUsers = array_map('trim', $match[1]);
		}

		preg_match_all('/для \[(.*?)]/iu', $message, $match);

		if (isset($match[1])) {
			$publicUsers = array_map('trim', $match[1]);

			if (count($privateUsers)) {
				$privateUsers = array_merge($privateUsers, $publicUsers);
				$privateUsers = array_unique($privateUsers);

				$publicUsers = [];
			}
		}

		$message = preg_replace('/(приватно|для) \[.*?]/iu', '', $message);

		$message = trim($message);
		$message = nl2br(str_replace(["\n", "\r"], '', $message));
		$message = '<span style="color: white">' . $message . '</span>';

		$users = count($privateUsers) ? $privateUsers : $publicUsers;
		$isPrivate = count($privateUsers) > 0;

		$receiversId = [];

		if (count($users)) {
			$receiversId = User::query()
				->where('username', $users)
				->get('id')->modelKeys();
		}

		$result = [
			'id' => $this->id,
			'time' => $this->created_at->timestamp,
			'user' => $this->user->username ?? '',
			'tou' => $users,
			'toi' => $receiversId,
			'text' => $message,
			'private' => $isPrivate > 0 ? 1 : 0,
			'me' => -1,
			'my' => -1,
		];

		$user = Auth::user();

		if ($user) {
			if (!$isPrivate && count($receiversId)) {
				$result['me'] = in_array($user->id, $receiversId) ? 1 : 0;
				$result['my'] = $this->user_id === $user->id ? 1 : 0;
			} elseif ($isPrivate && count($receiversId) && ($this->user_id === $user->id || in_array($user->id, $receiversId))) {
				$result['me'] = $this->user_id === $user->id ? 0 : 1;
				$result['my'] = $result['me'] ? 0 : 1;
			} elseif (!count($receiversId)) {
				$result['me'] = 0;
				$result['my'] = $this->user_id === $user->id ? 1 : 0;
			}
		}

		if ($result['me'] || $result['my']) {
			$this->isAllowed = true;
		}

		return $result;
	}
}
