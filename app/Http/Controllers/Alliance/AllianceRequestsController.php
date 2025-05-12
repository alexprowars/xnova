<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;

class AllianceRequestsController extends Controller
{
	use AllianceControllerTrait;

	public function index()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT) && !$alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$parse = [];
		$parse['items'] = [];

		$requests = AllianceRequest::query()
			->where('alliance_id', $alliance->id)
			->with('user')
			->orderByDesc('created_at')
			->get();

		foreach ($requests as $item) {
			$parse['items'][] = [
				'id' => $item->id,
				'name' => $item->user?->username,
				'message' => nl2br($item->message),
				'date' => $item->created_at->utc()->toAtomString(),
			];
		}

		return $parse;
	}

	public function accept(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		if ($alliance->members_count >= 150) {
			throw new Exception('Альянс не может иметь больше 150 участников');
		}

		$req = $alliance->requests()
			->where('id', (int) $request->post('id'))
			->first();

		if (!$req) {
			throw new Exception('Заявка не найдена');
		}

		if (!empty($request->post('message'))) {
			$message = strip_tags($request->post('message'));
		}

		$user = $req->user;

		AllianceRequest::query()->whereBelongsTo($user)->delete();
		AllianceMember::query()->whereBelongsTo($user)->delete();

		$alliance->members()->create([
			'user_id' => $user->id,
		]);

		$alliance->increment('members');

		$user->alliance_id = $alliance->id;
		$user->alliance_name = $alliance->name;
		$user->save();

		$user->notify(new MessageNotification($this->user->id, MessageType::Alliance, $alliance->tag, 'Привет!<br>Альянс <b>' . $alliance->name . '</b> принял вас в свои ряды!' . ((isset($message)) ? '<br>Приветствие:<br>' . $message : '')));
	}

	public function reject(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$req = $alliance->requests()
			->where('id', (int) $request->post('id'))
			->first();

		if (!$req) {
			throw new Exception('Заявка не найдена');
		}

		if (!empty($request->post('message'))) {
			$message = strip_tags($request->post('message'));
		}

		$req->delete();
		$req->user?->notify(new MessageNotification($this->user->id, MessageType::Alliance, $alliance->tag, 'Привет!<br>Альянс <b>' . $alliance->name . '</b> отклонил вашу кандидатуру!' . ((isset($message)) ? '<br>Причина:<br>' . $message : '')));
	}

	public function remove(int $id)
	{
		if (!$id) {
			throw new Exception('Не указан идентификатор заявки');
		}

		AllianceRequest::query()->where('id', $id)
			->whereBelongsTo($this->user)
			->delete();
	}
}
