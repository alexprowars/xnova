<?php

namespace App\Http\Resources;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Chat
 */
class ChatMessage extends JsonResource
{
	public function toArray($request)
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
				$privateUsers = array_merge($privateUsers, $publicUsers);
				$privateUsers = array_unique($privateUsers);

				$publicUsers = [];
			}
		}

		$message = preg_replace('/(приватно|для) \[.*?]/iu', '', $message);

		$message = trim($message);
		$message = nl2br(str_replace(["\n", "\r"], '', $message));

		if ($color = $this->user->getOption('color')) {
			$message = '<span style="color: ' . ___('main.colors.' . $color, 'white') . '">' . $message . '</span>';
		}

		$users = count($privateUsers) ? $privateUsers : $publicUsers;
		$isPrivate = count($privateUsers) > 0;

		$receiversId = [];

		if (count($users)) {
			$receiversId = User::query()
				->where('username', $users)
				->pluck('id')->all();
		}

		$result = [
			'id' => $this->id,
			'date' => $this->date->utc()->toAtomString(),
			'user' => $this->user->username ?? '',
			'tou' => $users,
			'toi' => $receiversId,
			'text' => $message,
			'private' => $isPrivate > 0,
			'me' => null,
			'my' => null,
		];

		$user = Auth::user();

		if ($user) {
			if (!$isPrivate && count($receiversId)) {
				$result['me'] = in_array($user->id, $receiversId);
				$result['my'] = $this->user_id === $user->id;
			} elseif ($isPrivate && count($receiversId) && ($this->user_id === $user->id || in_array($user->id, $receiversId))) {
				$result['me'] = $this->user_id !== $user->id;
				$result['my'] = !$result['me'];
			} elseif (!count($receiversId)) {
				$result['me'] = 0;
				$result['my'] = $this->user_id === $user->id;
			}
		}

		return $result;
	}
}
