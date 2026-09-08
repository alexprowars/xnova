<?php

namespace App\Http\Resources;

use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @property Chat $resource
 * @mixin Chat
 */
class ChatMessage extends JsonResource
{
	public function toArray($request): array
	{
		$message = $this->message;

		$recipients = $this->resource->getRecipients();

		$users = $recipients['users'];
		$isPrivate = $recipients['private'];

		$message = preg_replace('/(приватно|для) \[.*?]/iu', '', $message);

		$message = trim($message);
		$message = nl2br(str_replace(["\n", "\r"], '', $message));

		if ($color = $this->user->getOption('color')) {
			$message = '<span style="color: ' . ___('main.colors.' . $color, 'white') . '">' . $message . '</span>';
		}

		$receiversId = $this->recipient_ids ?? [];

		$user = Auth::user();

		if ($isPrivate && (!$user || ($this->user_id != $user->id && !in_array($user->id, $receiversId)))) {
			return [];
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
