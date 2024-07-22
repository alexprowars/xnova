<?php

namespace App\Http\Controllers;

use App\Engine\Enums\AllianceAccess;
use App\Engine\Enums\MessageType;
use App\Engine\Game;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Helpers;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;

class AllianceAdminController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::ADMIN_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$t = (int) $request->query('t', 1);

		if ($t != 1 && $t != 2 && $t != 3) {
			$t = 1;
		}

		if ($request->post('options')) {
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
				throw new Exception("Недопустимое значение атрибута!");
			}

			$alliance->update();
		} elseif ($request->post('t')) {
			if ($t == 3) {
				$alliance->request = Format::text($request->post('text', ''));
			} elseif ($t == 2) {
				$alliance->text = Format::text($request->post('text', ''));
			} else {
				$alliance->description = Format::text($request->post('text', ''));
			}

			$alliance->update();
		}

		if ($t == 3) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->request);
			$parse['Show_of_request_text'] = "Текст заявок альянса";
		} elseif ($t == 2) {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->text);
			$parse['Show_of_request_text'] = "Внутренний текст альянса";
		} else {
			$parse['text'] = preg_replace('!<br.*>!iU', "\n", $alliance->description);
		}

		$parse['t'] = $t;
		$parse['owner'] = $alliance->user_id;
		$parse['web'] = $alliance->web;
		$parse['access'] = $alliance->rights;

		$parse['image'] = '';

		if ((int) $alliance->image > 0) {
			$image = Files::getById($alliance->image);

			if ($image) {
				$parse['image'] = $image['src'];
			}
		}

		$parse['request_allow'] = $alliance->request_notallow;
		$parse['owner_range'] = $alliance->owner_range;

		$parse['can_view_members'] = $alliance->canAccess(AllianceAccess::CAN_KICK);

		return response()->state($parse);
	}

	public function rights(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new Exception(__('alliance.Denied_access'));
		} elseif (!empty($request->post('newrangname'))) {
			$ranks = $alliance->ranks;

			$rank = [
				'name' => strip_tags($request->post('newrangname')),
			];

			foreach (AllianceAccess::cases() as $case) {
				$rank[$case->value] = 0;
			}

			$ranks[] = $rank;

			$alliance->ranks = $ranks;
			$alliance->save();
		} elseif ($request->has('rigths') && is_array($request->post('rigths'))) {
			$rights = $request->post('rigths');

			$newRanks = $alliance->ranks;

			foreach ($alliance->ranks as $id => $rank) {
				$newRanks[$id] = array_merge($rank, [
					AllianceAccess::CAN_DELETE_ALLIANCE->value => $alliance->user_id == $this->user->id ? (isset($rights[$id][AllianceAccess::CAN_DELETE_ALLIANCE->value]) ? 1 : 0) : $rank[AllianceAccess::CAN_DELETE_ALLIANCE->value],
					AllianceAccess::CAN_KICK->value => $alliance->user_id == $this->user->id ? (isset($rights[$id][AllianceAccess::CAN_KICK->value]) ? 1 : 0) : $rank[AllianceAccess::CAN_KICK->value],
					AllianceAccess::REQUEST_ACCESS->value => isset($rights[$id][AllianceAccess::REQUEST_ACCESS->value]) ? 1 : 0,
					AllianceAccess::CAN_WATCH_MEMBERLIST->value => isset($rights[$id][AllianceAccess::CAN_WATCH_MEMBERLIST->value]) ? 1 : 0,
					AllianceAccess::CAN_ACCEPT->value => isset($rights[$id][AllianceAccess::CAN_ACCEPT->value]) ? 1 : 0,
					AllianceAccess::ADMIN_ACCESS->value => isset($rights[$id][AllianceAccess::ADMIN_ACCESS->value]) ? 1 : 0,
					AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS->value => isset($rights[$id][AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS->value]) ? 1 : 0,
					AllianceAccess::CHAT_ACCESS->value => isset($rights[$id][AllianceAccess::CHAT_ACCESS->value]) ? 1 : 0,
					AllianceAccess::CAN_EDIT_RIGHTS->value => isset($rights[$id][AllianceAccess::CAN_EDIT_RIGHTS->value]) ? 1 : 0,
					AllianceAccess::DIPLOMACY_ACCESS->value => isset($rights[$id][AllianceAccess::DIPLOMACY_ACCESS->value]) ? 1 : 0,
				]);
			}

			$alliance->ranks = $newRanks;
			$alliance->save();
		} elseif ($request->query('d') && isset($alliance->ranks[$request->query('d', 'int')])) {
			unset($alliance->ranks[$request->query('d', 'int')]);
			$alliance->save();
		}

		$parse['alliance'] = $alliance->only(['id', 'user_id']);
		$parse['list'] = [];

		foreach ($alliance->ranks as $a => $b) {
			$rights = [];

			foreach (AllianceAccess::cases() as $case) {
				$rights[$case->value] = (bool) ($b[$case->value] ?: false);
			}

			$parse['list'][] = [
				'id' => $a,
				'name' => $b['name'],
				'rights' => $rights
			];
		}

		return response()->state($parse);
	}

	public function requests(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_ACCEPT) && !$alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$show = (int) $request->query('show', 0);

		if (($alliance->user_id == $this->user->id || $alliance->canAccess(AllianceAccess::CAN_ACCEPT)) && $request->post('action')) {
			if ($request->post('action') == "Принять") {
				if ($alliance->members_count >= 150) {
					throw new Exception('Альянс не может иметь больше 150 участников');
				}

				if ($request->post('text') != '') {
					$text_ot = strip_tags($request->post('text'));
				}

				$check = AllianceRequest::query()
					->where('alliance_id', $alliance->id)
					->where('user_id', $show)
					->exists();

				if ($check) {
					AllianceRequest::query()->where('user_id', $show)->delete();
					AllianceMember::query()->where('user_id', $show)->delete();

					AllianceMember::insert([
						'alliance_id' => $alliance->id,
						'user_id' => $show,
						'time' => time(),
					]);

					Alliance::query()->where('id', $alliance->id)->increment('members');
					User::query()->where('id', $show)->update([
						'alliance_name' => $alliance->name,
						'alliance_id' => $alliance->id,
					]);

					User::find($show)?->notify(new MessageNotification($this->user->id, MessageType::Alliance, $alliance->tag, "Привет!<br>Альянс <b>" . $alliance->name . "</b> принял вас в свои ряды!" . ((isset($text_ot)) ? "<br>Приветствие:<br>" . $text_ot : "")));

					throw new RedirectException('/alliance/members', 'Игрок принят в альянс');
				}
			} elseif ($request->post('action') == "Отклонить") {
				if ($request->post('text') != '') {
					$text_ot = strip_tags($request->post('text'));
				}

				AllianceRequest::query()->where('user_id', $show)->where('alliance_id', $alliance->id)->delete();

				User::find($show)?->notify(new MessageNotification($this->user->id, MessageType::Alliance, $alliance->tag, "Привет!<br>Альянс <b>" . $alliance->name . "</b> отклонил вашу кандидатуру!" . ((isset($text_ot)) ? "<br>Причина:<br>" . $text_ot : "")));
			}
		}

		$parse = [];
		$parse['list'] = [];

		$requests = AllianceRequest::query()
			->where('alliance_id', $alliance->id)
			->with('user')
			->get();

		foreach ($requests as $item) {
			if ($item->id == $show) {
				$s = [];
				$s['username'] = $item->user?->username;
				$s['request_text'] = nl2br($item->message);
				$s['id'] = $item->id;
			}

			$item->created_at = Game::datezone('Y-m-d H:i:s', $request->created_at);

			$parse['list'][] = $item;
		}

		if ($show != 0 && count($parse['list']) > 0 && isset($s)) {
			$parse['request'] = $s;
		} else {
			$parse['request'] = null;
		}

		$parse['tag'] = $alliance->tag;

		return response()->state($parse);
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

	public function give(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id) {
			throw new RedirectException('/alliance', 'Доступ запрещён.');
		}

		if ($request->post('newleader') && $alliance->user_id == $this->user->id) {
			$info = User::find($request->post('newleader'));

			if (!$info || $info->alliance_id != $this->user->alliance_id) {
				throw new RedirectException('/alliance', 'Операция невозможна.');
			}

			$alliance->user_id = $info->id;
			$alliance->save();

			AllianceMember::query()->where('user_id', $info->id)
				->update(['rank' => 0]);

			throw new RedirectException('/alliance', 'Правление передано');
		}

		$listuser = AllianceMember::query()
			->where('alliance_id', $alliance->id)
			->where('user_id', $alliance->user_id)
			->where('rank', '>', 0)
			->with('user')
			->get();

		$parse['righthand'] = '';

		foreach ($listuser as $u) {
			if ($alliance->ranks[$u->rank][AllianceAccess::CAN_EDIT_RIGHTS->value] == 1) {
				$parse['righthand'] .= "<option value=\"" . $u->id . "\">" . $u->user?->username . "&nbsp;[" . $alliance->ranks[$u->rank]['name'] . "]&nbsp;&nbsp;</option>";
			}
		}

		$parse['id'] = $this->user->id;

		return response()->state($parse);
	}

	public function members(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_KICK)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		if ($request->query('kick')) {
			$kick = $request->query('kick', 0);

			if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_KICK) && $kick > 0) {
				throw new Exception(__('alliance.Denied_access'));
			}

			$u = User::find($kick);

			if ($u && $u->alliance_id == $alliance->id && $u->id != $alliance->user_id) {
				Planet::query()->where('user_id', $u->id)
					->where('alliance_id', $alliance->id)
					->update(['alliance_id' => null]);

				$u->alliance_id = null;
				$u->alliance_name = null;
				$u->save();

				AllianceMember::query()->where('user_id', $u->id)->delete();
			} else {
				throw new Exception(__('alliance.Denied_access'));
			}
		} elseif ($request->post('newrang', '') != '' && $request->input('id', 0) != 0) {
			$id = $request->input('id', 0);
			$rank = $request->post('newrang');

			$q = User::find($id);

			if ((isset($alliance->ranks[$rank]) || $rank == null) && $q->id != $alliance->user_id && $q->alliance_id == $alliance->id) {
				$alliance->members()->where('user_id', $q->id)
					->update(['rank' => $rank]);
			}
		}

		return $this->members($request);
	}
}
