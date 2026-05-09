<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\AllianceMemberAcceptMessage;
use App\Engine\Messages\Types\AllianceMemberRejectMessage;
use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AllianceRequestsController extends Controller
{
	use AllianceControllerTrait;

	public function index()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT) && !$alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$result = [];

		$requests = AllianceRequest::query()
			->where('alliance_id', $alliance->id)
			->with('user')
			->orderByDesc('created_at')
			->get();

		foreach ($requests as $item) {
			$result[] = [
				'id' => $item->id,
				'name' => $item->user?->username,
				'message' => nl2br($item->message),
				'date' => $item->created_at->utc()->toAtomString(),
			];
		}

		return Inertia::render('Alliance/Admin/Requests', [
			'items' => $result,
		]);
	}

	public function accept(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		if ($alliance->total_members >= 150) {
			throw new PageException('Альянс не может иметь больше 150 участников');
		}

		$req = $alliance->requests()
			->where('id', (int) $request->post('id'))
			->first();

		if (!$req) {
			throw new PageException('Заявка не найдена');
		}

		if (!empty($request->post('message'))) {
			$requestMessage = strip_tags($request->post('message'));
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

		$notification = new MessageNotification(
			$this->user->id,
			MessageType::Alliance,
			$alliance->tag,
			new AllianceMemberAcceptMessage([
				'name' => $alliance->name,
				'message' => $requestMessage ?? null,
			])
		);

		$user->notify($notification);

		return to_route('alliance.members');
	}

	public function reject(Request $request): void
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$req = $alliance->requests()
			->where('id', (int) $request->post('id'))
			->first();

		if (!$req) {
			throw new PageException('Заявка не найдена');
		}

		if (!empty($request->post('message'))) {
			$requestMessage = strip_tags($request->post('message'));
		}

		$req->delete();

		$notification = new MessageNotification(
			$this->user->id,
			MessageType::Alliance,
			$alliance->tag,
			new AllianceMemberRejectMessage([
				'name' => $alliance->name,
				'message' => $requestMessage ?? null,
			])
		);

		$req->user?->notify($notification);
	}

	public function remove(int $id): void
	{
		if (!$id) {
			throw new PageException('Не указан идентификатор заявки');
		}

		AllianceRequest::query()->where('id', $id)
			->whereBelongsTo($this->user)
			->delete();
	}
}
