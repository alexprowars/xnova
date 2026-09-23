<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models\Alliance;
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
			throw new Exception(__('alliance.denied_access'));
		}

		$type = $request->integer('type', 1);

		if ($type != 1 && $type != 2 && $type != 3) {
			$type = 1;
		}

		if ($type == 3) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->request);
		} elseif ($type == 2) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->text);
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

		return Inertia::render('Alliance/Admin/Main', $parse);
	}

	public function namePage()
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.denied_access'));
		}

		return Inertia::render('Alliance/Admin/Name', [
			'name' => $alliance->name,
		]);
	}

	public function name(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.denied_access'));
		}

		$name = addslashes(htmlspecialchars(trim($request->post('name', ''))));

		if (empty($name)) {
			throw new PageException(__('alliance.new_name_required'));
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new PageException(__('alliance.invalid_name'));
		}

		if (mb_strlen($name) > 32) {
			throw new PageException(__('alliance.name_too_long'));
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
			throw new Exception(__('alliance.denied_access'));
		}

		return Inertia::render('Alliance/Admin/Tag', [
			'tag' => $alliance->tag,
		]);
	}

	public function tag(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.denied_access'));
		}

		$tag = trim($request->post('tag', ''));

		if (empty($tag)) {
			throw new PageException(__('alliance.new_tag_required'));
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new PageException(__('alliance.invalid_tag'));
		}

		$tag = addslashes(htmlspecialchars($tag));

		if (mb_strlen($tag) > 8) {
			throw new PageException(__('alliance.tag_too_long'));
		}

		$tagExists = Alliance::query()
			->where('tag', $tag)
			->whereNot('id', $alliance->id)
			->exists();

		if ($tagExists) {
			throw new PageException(str_replace('%s', $tag, __('alliance.always_exist')));
		}

		$alliance->tag = $tag;
		$alliance->save();

		return to_route('alliance.admin');
	}

	public function remove()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_DELETE_ALLIANCE)) {
			throw new PageException(__('alliance.denied_access'));
		}

		$alliance->delete();

		return to_route('alliance');
	}

	public function give()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new PageException(__('alliance.denied_access'));
		}

		$members = $alliance->members()
			->whereNot('user_id', $alliance->user_id)
			->whereNotNull('rank')
			->with('user')
			->get();

		$result = [
			'members' => [],
		];

		foreach ($members as $member) {
			if (($alliance->ranks[$member->rank][AllianceAccess::CAN_EDIT_RIGHTS->value] ?? 0) == 1) {
				$result['members'][] = [
					'id' => $member->id,
					'name' => $member->user?->username,
					'rank' => $alliance->ranks[$member->rank]['name'],
				];
			}
		}

		return Inertia::render('Alliance/Admin/Give', $result);
	}

	public function giveSend(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new PageException(__('alliance.denied_access'));
		}

		$member = $alliance->members()->with('user')
			->find((int) $request->post('member', 0));

		$user = $member?->user;

		if (!$user || $user->alliance_id != $this->user->alliance_id) {
			throw new PageException(__('alliance.operation_impossible'));
		}

		$alliance->user()->associate($user);
		$alliance->save();

		$member->update(['rank' => null]);

		return to_route('alliance');
	}

	public function update(Request $request): void
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new PageException(__('alliance.denied_access'));
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
					['image' => $file],
					['image' => ['image', 'mimetypes:image/jpeg,image/webp,image/png']]
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
			throw new Exception(__('alliance.denied_access'));
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
