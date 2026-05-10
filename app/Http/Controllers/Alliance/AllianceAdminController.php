<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models\AllianceMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Throwable;

class AllianceAdminController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$type = $request->integer('type', 1);

		if ($type != 1 && $type != 2 && $type != 3) {
			$type = 1;
		}

		if ($type == 3) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->request);
			$parse['Show_of_request_text'] = 'Текст заявок альянса';
		} elseif ($type == 2) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->text);
			$parse['Show_of_request_text'] = 'Внутренний текст альянса';
		} else {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->description);
		}

		$parse['text_type'] = $type;
		$parse['owner'] = $alliance->user_id;
		$parse['web'] = $alliance->web;
		$parse['access'] = $alliance->rights;
		$parse['image'] = $alliance->getFirstMediaUrl(conversionName: 'thumb');
		$parse['public'] = $alliance->public;
		$parse['owner_rank'] = $alliance->owner_rank;

		return Inertia::render('Alliance/Admin/Main', [
			'data' => $parse,
		]);
	}

	public function namePage()
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		return Inertia::render('Alliance/Admin/Name', [
			'name' => $alliance->name,
		]);
	}

	public function name(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$name = addslashes(htmlspecialchars(trim($request->post('name', ''))));

		if (empty($name)) {
			throw new PageException('Введите новое название альянса');
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new PageException('Название альянса содержит запрещённые символы');
		}

		$alliance->name = $name;
		$alliance->update();

		User::query()->where('alliance_id', $alliance->id)
			->update(['alliance_name' => $alliance->name]);

		return to_route('alliance.admin');
	}

	public function tagPage()
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		return Inertia::render('Alliance/Admin/Tag', [
			'tag' => $alliance->tag,
		]);
	}

	public function tag(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$tag = trim($request->post('tag', ''));

		if (empty($tag)) {
			throw new PageException('Введите новую абревиатуру альянса');
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new PageException('Абревиатура альянса содержит запрещённые символы');
		}

		$alliance->tag = addslashes(htmlspecialchars($tag));
		$alliance->save();

		return to_route('alliance.admin');
	}

	public function remove()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_DELETE_ALLIANCE)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$alliance->delete();

		return to_route('alliance');
	}

	public function give()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new PageException('Доступ запрещён');
		}

		$members = $alliance->members()
			->whereNot('user_id', $alliance->user_id)
			->where('rank', '>', 0)
			->with('user')
			->get();

		$result = [
			'members' => [],
		];

		foreach ($members as $member) {
			if ($alliance->ranks[$member->rank][AllianceAccess::CAN_EDIT_RIGHTS->value] == 1) {
				$result['members'][] = [
					'id' => $member->id,
					'name' => $member->user?->username,
					'rank' => $alliance->ranks[$member->rank]['name'],
				];
			}
		}

		return Inertia::render('Alliance/Admin/Give', [
			'data' => $result,
		]);
	}

	public function giveSend(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new PageException('Доступ запрещён');
		}

		$user = User::find((int) $request->post('user', 0));

		if (!$user || $user->alliance_id != $this->user->alliance_id) {
			throw new PageException('Операция невозможна.');
		}

		$alliance->user()->associate($user);
		$alliance->save();

		AllianceMember::query()->whereBelongsTo($user)
			->update(['rank' => 0]);

		return to_route('alliance');
	}

	public function update(Request $request): void
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.Denied_access'));
		}

		if ($request->has('owner_rank')) {
			$alliance->owner_rank = Str::sanitize(strip_tags($request->post('owner_rank', '')));
		}

		if ($request->has('web')) {
			$alliance->web = Str::sanitize(strip_tags($request->post('web', '')));
		}

		if ($request->hasFile('image')) {
			$file = $request->file('image');

			if ($file->isValid()) {
				$validator = Validator::make(
					['file' => $file],
					['image' => 'image,mimetypes:image/jpg,image/webp,image/png']
				);

				if ($validator->passes()) {
					$alliance->clearMediaCollection();

					try {
						$alliance->addMedia($file)->toMediaCollection();
					} catch (Throwable $e) {
						Log::error($e);
					}
				}
			}
		}

		if ($request->post('delete_image')) {
			$alliance->clearMediaCollection();
		}

		if ($request->has('public')) {
			$alliance->public = $request->integer('public') > 0;
		}

		$alliance->update();
	}

	public function text(Request $request): void
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$type = (int) $request->post('type', 1);

		if ($type != 1 && $type != 2 && $type != 3) {
			$type = 1;
		}

		$text = $request->post('text', '');

		if ($type == 3) {
			$alliance->request = Format::text($text);
		} elseif ($type == 2) {
			$alliance->text = Format::text($text);
		} else {
			$alliance->description = Format::text($text);
		}

		$alliance->update();
	}
}
