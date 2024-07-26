<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models\AllianceChat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllianceChatController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		if ($this->user->messages_ally != 0) {
			$this->user->messages_ally = 0;
			$this->user->update();
		}

		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CHAT_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$parse = [];
		$parse['items'] = [];

		$messagesCount = AllianceChat::query()
			->whereBelongsTo($alliance)
			->count();

		$parse['pagination'] = [
			'total' => $messagesCount,
			'limit' => 10,
			'page' => (int) $request->query('p', 1),
		];

		if ($messagesCount > 0) {
			$messages = AllianceChat::query()
				->whereBelongsTo($alliance)
				->orderByDesc('id')
				->limit($parse['pagination']['limit'])
				->offset(($parse['pagination']['page'] - 1) * $parse['pagination']['limit'])
				->get();

			foreach ($messages as $message) {
				$parse['items'][] = [
					'id' => (int) $message->id,
					'user' => $message->user,
					'user_id' => (int) $message->user_id,
					'time' => $message->timestamp?->utc()->toAtomString(),
					'message' => str_replace(["\r\n", "\n", "\r"], '', stripslashes($message->message)),
				];
			}
		}

		$parse['owner'] = $alliance->user_id == $this->user->id;

		return response()->state($parse);
	}

	public function send(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CHAT_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$message = $request->post('message');

		if (empty($message)) {
			throw new Exception('Введите сообщение');
		}

		AllianceChat::create([
			'alliance_id' 	=> $this->user->alliance_id,
			'user' 			=> $this->user->username,
			'user_id' 		=> $this->user->id,
			'message' 		=> Format::text($message),
			'timestamp'		=> now(),
		]);

		User::query()->where('alliance_id', $this->user->alliance_id)
			->where('id', '!=', $this->user->id)
			->update(['messages_ally' => DB::raw('messages_ally + 1')]);
	}

	public function delete(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$type = $request->post('type', 'marked');

		if ($type == 'all') {
			AllianceChat::query()->whereBelongsTo($alliance)->delete();
		}

		if ($type == 'marked' || $type == 'unmarked') {
			$messages = $request->post('id');

			if (is_array($messages)) {
				$messages = array_map('intval', $messages);

				if (count($messages)) {
					$query = AllianceChat::query()->whereBelongsTo($alliance);

					if ($type == 'unmarked') {
						$query->whereNotIn('id', $messages);
					} else {
						$query->whereIn('id', $messages);
					}

					$query->delete();
				}
			}
		}
	}
}
