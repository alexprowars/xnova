<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AllianceMember;
use App\Models\User;
use Illuminate\Http\Request;

class AllianceAdminController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new RedirectException('/alliance', __('alliance.Denied_access'));
		}

		$type = (int) $request->get('type', 1);

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
		$parse['image'] = null;

		if ($alliance->image) {
			$image = Files::getById($alliance->image);

			if ($image) {
				$parse['image'] = $image['src'];
			}
		}

		$parse['request_allow'] = $alliance->request_notallow;
		$parse['owner_range'] = $alliance->owner_range;

		return $parse;
	}

	public function name(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$name = addslashes(htmlspecialchars(trim($request->post('name', ''))));

		if (empty($name)) {
			throw new Exception('Введите новое название альянса');
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new Exception('Название альянса содержит запрещённые символы');
		}

		$alliance->name = $name;
		$alliance->update();

		User::query()->where('alliance_id', $alliance->id)
			->update(['alliance_name' => $alliance->name]);
	}

	public function tag(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$tag = trim($request->post('tag', ''));

		if (empty($tag)) {
			throw new Exception('Введите новую абревиатуру альянса');
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new Exception('Абревиатура альянса содержит запрещённые символы');
		}

		$alliance->tag = addslashes(htmlspecialchars($tag));
		$alliance->save();
	}

	public function remove()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_DELETE_ALLIANCE)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$alliance->delete();
	}

	public function give()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new RedirectException('/alliance', 'Доступ запрещён');
		}

		$listuser = $alliance->members()
			->whereNot('user_id', $alliance->user_id)
			->where('rank', '>', 0)
			->with('user')
			->get();

		$parse['users'] = [];

		foreach ($listuser as $u) {
			if ($alliance->ranks[$u->rank][AllianceAccess::CAN_EDIT_RIGHTS->value] == 1) {
				$parse['users'][] = [
					'id' => $u->id,
					'name' => $u->user?->username,
					'rank' => $alliance->ranks[$u->rank]['name'],
				];
			}
		}

		$parse['id'] = $this->user->id;

		return $parse;
	}

	public function giveSend(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new Exception('Доступ запрещён');
		}

		$user = User::find((int) $request->post('user', 0));

		if (!$user || $user->alliance_id != $this->user->alliance_id) {
			throw new Exception('Операция невозможна.');
		}

		$alliance->user_id = $user->id;
		$alliance->save();

		AllianceMember::query()->where('user_id', $user->id)
			->update(['rank' => 0]);
	}

	public function update(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$alliance->owner_range = Helpers::checkString($request->post('owner_range', ''), true);
		$alliance->web = Helpers::checkString($request->post('web', ''), true);

		if ($request->hasFile('image')) {
			$file = $request->file('image');

			if ($file->isValid()) {
				$fileType = $file->getMimeType();

				if (!str_contains($fileType, 'image/')) {
					throw new Exception('Разрешены к загрузке только изображения');
				}

				if ($alliance->image > 0) {
					Files::delete($alliance->image);
				}

				$alliance->image = Files::save($file);
			}
		}

		if ($request->post('delete_image') && Files::delete($alliance->image)) {
			$alliance->image = 0;
		}

		$alliance->request_notallow = (int) $request->post('request_notallow', 0);

		if ($alliance->request_notallow != 0 && $alliance->request_notallow != 1) {
			throw new Exception('Недопустимое значение атрибута!');
		}

		$alliance->update();
	}

	public function text(Request $request)
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
