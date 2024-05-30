<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Game;
use App\Helpers;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use App\Models;
use App\Models\User;
use App\Controller;

class AllianceController extends Controller
{
	protected function getAlliance()
	{
		$alliance = $this->user->alliance;
		$alliance->getRanks();

		$member = $alliance->getMember($this->user->id);

		if (!$member) {
			$member = new AllianceMember();
			$member->user_id = $this->user->id;
			$member->save();

			if ($member = $alliance->members()->save($member)) {
				$alliance->member = $member;
			}
		}

		return $alliance;
	}

	private function noAlly(Request $request)
	{
		if ($request->post('bcancel') && $request->post('r_id')) {
			AllianceRequest::where('alliance_id', (int) $request->post('r_id'))
				->where('user_id', $this->user->id)
				->delete();

			throw new RedirectException('/alliance', 'Вы отозвали свою заявку на вступление в альянс');
		}

		$parse = [];

		$parse['list'] = [];

		$requests = AllianceRequest::query()
			->where('user_id', $this->user->id)
			->with('alliance')
			->get();

		foreach ($requests as $item) {
			$parse['list'][] = [$item->alliance_id, $item->alliance?->tag, $item->alliance?->name, $item->created_at?->utc()->toAtomString()];
		}

		$parse['allys'] = [];

		$alliances = DB::table('alliances', 'a')
			->select('a.id, a.tag, a.name, a.members_count as members, s.total_points')
			->from('statistics', 's')
			->where('s.stat_type', 2)
			->where('s.stat_code', 1)
			->where('s.alliance_id', 'a.id')
			->orderByDesc('s.total_points')
			->limit(15);

		foreach ($alliances as $item) {
			$parse['allys'][] = (array) $item;
		}

		return response()->state($parse);
	}

	public function index(Request $request)
	{
		if ($this->user->alliance_id == 0) {
			return $this->noAlly($request);
		}

		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			$range = ($alliance->owner_range == '') ? 'Основатель' : $alliance->owner_range;
		} elseif ($alliance->member->rank != null && isset($alliance->ranks[$alliance->member->rank]['name'])) {
			$range = $alliance->ranks[$alliance->member->rank]['name'];
		} else {
			$range = __('alliance.member');
		}

		$parse['range'] = $range;

		$parse['diplomacy'] = false;

		if ($alliance->canAccess(Alliance::DIPLOMACY_ACCESS)) {
			$parse['diplomacy'] = Models\AllianceDiplomacy::query()->where('diplomacy_id', $alliance->id)->where('status', 0)->count();
		}

		$parse['requests'] = 0;

		if ($alliance->user_id == $this->user->id || $alliance->canAccess(Alliance::REQUEST_ACCESS)) {
			$parse['requests'] = Models\AllianceDiplomacy::query()->where('alliance_id', $alliance->id)->count();
		}

		$parse['alliance_admin'] = $alliance->canAccess(Alliance::ADMIN_ACCESS);
		$parse['chat_access'] = $alliance->canAccess(Alliance::CHAT_ACCESS);
		$parse['members_list'] = $alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST);
		$parse['owner'] = ($alliance->user_id != $this->user->id) ? $this->MessageForm(__('alliance.Exit_of_this_alliance'), "", "/alliance/exit/", __('alliance.Continue')) : '';

		$parse['image'] = '';

		if ((int) $alliance->image > 0) {
			$image = Files::getById($alliance->image);

			if ($image) {
				$parse['image'] = $image['src'];
			}
		}

		$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->description));
		$parse['text'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->text));

		$parse['web'] = $alliance->web;

		if ($parse['web'] != '' && !str_contains($parse['web'], 'http')) {
			$parse['web'] = 'https://' . $parse['web'];
		}

		$parse['tag'] = $alliance->tag;
		$parse['members'] = $alliance->members_count;
		$parse['name'] = $alliance->name;
		$parse['id'] = $alliance->id;

		return response()->state($parse);
	}

	public function admin(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(Alliance::ADMIN_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
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
						throw new ErrorException('Разрешены к загрузке только изображения');
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
				throw new ErrorException("Недопустимое значение атрибута!");
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

		$parse['image'] = '';

		if ((int) $alliance->image > 0) {
			$image = Files::getById($alliance->image);

			if ($image) {
				$parse['image'] = $image['src'];
			}
		}

		$parse['request_allow'] = $alliance->request_notallow;
		$parse['owner_range'] = $alliance->owner_range;

		$parse['can_view_members'] = $alliance->canAccess(Alliance::CAN_KICK);

		if ($alliance->user_id == $this->user->id) {
			$parse['Transfer_alliance'] = $this->MessageForm("Покинуть / Передать альянс", "", "/alliance/admin/give", 'Продолжить');
		}

		if ($alliance->canAccess(Alliance::CAN_DELETE_ALLIANCE)) {
			$parse['Disolve_alliance'] = $this->MessageForm("Расформировать альянс", "", "/alliance/admin/exit", 'Продолжить');
		}

		return response()->state($parse);
	}

	public function adminRights(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(Alliance::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new ErrorException(__('alliance.Denied_access'));
		} elseif (!empty($request->post('newrangname'))) {
			$ranks = $alliance->ranks;
			$ranks[] = [
				'name' => strip_tags($request->post('newrangname')),
				Alliance::CAN_DELETE_ALLIANCE => 0,
				Alliance::CAN_KICK => 0,
				Alliance::REQUEST_ACCESS => 0,
				Alliance::CAN_WATCH_MEMBERLIST => 0,
				Alliance::CAN_ACCEPT => 0,
				Alliance::ADMIN_ACCESS => 0,
				Alliance::CAN_WATCH_MEMBERLIST_STATUS => 0,
				Alliance::CHAT_ACCESS => 0,
				Alliance::CAN_EDIT_RIGHTS => 0,
				Alliance::DIPLOMACY_ACCESS => 0,
			];

			$alliance->ranks = $ranks;
			$alliance->save();
		} elseif ($request->has('rigths') && is_array($request->post('rigths'))) {
			$rights = $request->post('rigths');

			$newRanks = $alliance->ranks;

			foreach ($alliance->ranks as $id => $rank) {
				$newRanks[$id] = array_merge($rank, [
					Alliance::CAN_DELETE_ALLIANCE => $alliance->user_id == $this->user->id ? (isset($rights[$id][Alliance::CAN_DELETE_ALLIANCE]) ? 1 : 0) : $rank[Alliance::CAN_DELETE_ALLIANCE],
					Alliance::CAN_KICK => $alliance->user_id == $this->user->id ? (isset($rights[$id][Alliance::CAN_KICK]) ? 1 : 0) : $rank[Alliance::CAN_KICK],
					Alliance::REQUEST_ACCESS => isset($rights[$id][Alliance::REQUEST_ACCESS]) ? 1 : 0,
					Alliance::CAN_WATCH_MEMBERLIST => isset($rights[$id][Alliance::CAN_WATCH_MEMBERLIST]) ? 1 : 0,
					Alliance::CAN_ACCEPT => isset($rights[$id][Alliance::CAN_ACCEPT]) ? 1 : 0,
					Alliance::ADMIN_ACCESS => isset($rights[$id][Alliance::ADMIN_ACCESS]) ? 1 : 0,
					Alliance::CAN_WATCH_MEMBERLIST_STATUS => isset($rights[$id][Alliance::CAN_WATCH_MEMBERLIST_STATUS]) ? 1 : 0,
					Alliance::CHAT_ACCESS => isset($rights[$id][Alliance::CHAT_ACCESS]) ? 1 : 0,
					Alliance::CAN_EDIT_RIGHTS => isset($rights[$id][Alliance::CAN_EDIT_RIGHTS]) ? 1 : 0,
					Alliance::DIPLOMACY_ACCESS => isset($rights[$id][Alliance::DIPLOMACY_ACCESS]) ? 1 : 0,
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
			$parse['list'][] = [
				'id' => $a,
				'name' => $b['name'],
				'rights' => [
					Alliance::CAN_DELETE_ALLIANCE => (bool) ($b[Alliance::CAN_DELETE_ALLIANCE] ?: false),
					Alliance::CAN_KICK => (bool) ($b[Alliance::CAN_KICK] ?: false),
					Alliance::REQUEST_ACCESS => (bool) ($b[Alliance::REQUEST_ACCESS] ?: false),
					Alliance::CAN_WATCH_MEMBERLIST => (bool) ($b[Alliance::CAN_WATCH_MEMBERLIST] ?: false),
					Alliance::CAN_ACCEPT => (bool) ($b[Alliance::CAN_ACCEPT] ?: false),
					Alliance::ADMIN_ACCESS => (bool) ($b[Alliance::ADMIN_ACCESS] ?: false),
					Alliance::CAN_WATCH_MEMBERLIST_STATUS => (bool) ($b[Alliance::CAN_WATCH_MEMBERLIST_STATUS] ?: false),
					Alliance::CHAT_ACCESS => (bool) ($b[Alliance::CHAT_ACCESS] ?: false),
					Alliance::CAN_EDIT_RIGHTS => (bool) ($b[Alliance::CAN_EDIT_RIGHTS] ?: false),
					Alliance::DIPLOMACY_ACCESS => (bool) ($b[Alliance::DIPLOMACY_ACCESS] ?: false),
				]
			];
		}

		return response()->state($parse);
	}

	public function adminRequests(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CAN_ACCEPT) && !$alliance->canAccess(Alliance::REQUEST_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$show = (int) $request->query('show', 0);

		if (($alliance->user_id == $this->user->id || $alliance->canAccess(Alliance::CAN_ACCEPT)) && $request->post('action')) {
			if ($request->post('action') == "Принять") {
				if ($alliance->members_count >= 150) {
					throw new ErrorException('Альянс не может иметь больше 150 участников');
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

					User::sendMessage($show, $this->user->id, 0, 3, $alliance->tag, "Привет!<br>Альянс <b>" . $alliance->name . "</b> принял вас в свои ряды!" . ((isset($text_ot)) ? "<br>Приветствие:<br>" . $text_ot . "" : ""));

					throw new RedirectException('/alliance/members', 'Игрок принят в альянс');
				}
			} elseif ($request->post('action') == "Отклонить") {
				if ($request->post('text') != '') {
					$text_ot = strip_tags($request->post('text'));
				}

				AllianceRequest::query()->where('user_id', $show)->where('alliance_id', $alliance->id)->delete();

				User::sendMessage($show, $this->user->id, 0, 3, $alliance->tag, "Привет!<br>Альянс <b>" . $alliance->name . "</b> отклонил вашу кандидатуру!" . ((isset($text_ot)) ? "<br>Причина:<br>" . $text_ot . "" : ""));
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

	public function adminName(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(Alliance::ADMIN_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		if ($request->post('name')) {
			$name = trim($request->post('name', ''));

			if (empty($name)) {
				throw new ErrorException("Введите новое название альянса");
			}

			if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
				throw new ErrorException("Название альянса содержит запрещённые символы");
			}

			$alliance->name = addslashes(htmlspecialchars($name));
			$alliance->update();

			User::query()->where('alliance_id', $alliance->id)
				->update(['alliance_name', $alliance->name]);

			throw new RedirectException('/alliance/admin/name', 'Название альянса изменено');
		}

		return response()->state([
			'name' => $alliance->name
		]);
	}

	public function adminTag(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(Alliance::ADMIN_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		if ($request->post('tag')) {
			$tag = trim($request->post('tag', ''));

			if ($tag == '') {
				throw new RedirectException('/alliance/admin/tag', "Введите новую абревиатуру альянса");
			}

			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
				throw new ErrorException("Абревиатура альянса содержит запрещённые символы");
			}

			$alliance->tag = addslashes(htmlspecialchars($tag));
			$alliance->save();

			throw new RedirectException('/alliance/admin/tag', 'Абревиатура альянса изменена');
		}

		return response()->state([
			'tag' => $alliance->tag
		]);
	}

	public function adminExit()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CAN_DELETE_ALLIANCE)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$alliance->delete();

		throw new RedirectException('/alliance', 'Альянс удалён');
	}

	public function adminGive(Request $request)
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
			if ($alliance->ranks[$u->rank][Alliance::CAN_EDIT_RIGHTS] == 1) {
				$parse['righthand'] .= "<option value=\"" . $u->id . "\">" . $u->user?->username . "&nbsp;[" . $alliance->ranks[$u->rank]['name'] . "]&nbsp;&nbsp;</option>";
			}
		}

		$parse['id'] = $this->user->id;

		return response()->state($parse);
	}

	public function adminMembers(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CAN_KICK)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		if ($request->query('kick')) {
			$kick = $request->query('kick', 0);

			if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CAN_KICK) && $kick > 0) {
				throw new ErrorException(__('alliance.Denied_access'));
			}

			$u = User::find($kick);

			if ($u && $u->alliance_id == $alliance->id && $u->id != $alliance->user_id) {
				Models\Planet::query()->where('user_id', $u->id)
					->where('alliance_id', $alliance->id)
					->update(['alliance_id' => null]);

				$u->alliance_id = null;
				$u->alliance_name = null;
				$u->save();

				AllianceMember::query()->where('user_id', $u->id)->delete();
			} else {
				throw new ErrorException(__('alliance.Denied_access'));
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

	public function diplomacy(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::DIPLOMACY_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$parse['DText'] = $parse['DMyQuery'] = $parse['DQuery'] = [];

		if ($request->query('edit')) {
			if ($request->query('edit', '') == "add") {
				$st = (int) $request->post('status', 0);
				$al = Alliance::find((int) $request->post('ally'));

				if (!$al) {
					throw new RedirectException("/alliance/diplomacy", "Ошибка ввода параметров");
				}

				$ad = Models\AllianceDiplomacy::query()->where('alliance_id', $alliance->id)
					->where('diplomacy_id', $al->id)
					->count();

				if ($ad) {
					throw new RedirectException("/alliance/diplomacy", "У вас уже есть соглашение с этим альянсом. Разорвите старое соглашения прежде чем создать новое.");
				}

				if ($st < 0 || $st > 3) {
					$st = 0;
				}

				Models\AllianceDiplomacy::create([
					'alliance_id' => $alliance->id,
					'diplomacy_id' => $al->id,
					'type' => $st,
					'status' => 0,
					'primary' => 1,
				]);

				Models\AllianceDiplomacy::create([
					'alliance_id' => $al->id,
					'diplomacy_id' => $alliance->id,
					'type' => $st,
					'status' => 0,
					'primary' => 0,
				]);

				throw new RedirectException('/alliance/diplomacy', 'Отношение между вашими альянсами успешно добавлено');
			} elseif ($request->query('edit', '') == 'del') {
				$al = Models\AllianceDiplomacy::query()->where('id', (int) $request->query('id'))
					->where('alliance_id', $alliance->id)
					->first();

				if (!$al) {
					throw new RedirectException('/alliance/diplomacy', 'Ошибка ввода параметров');
				}

				Models\AllianceDiplomacy::query()->where('alliance_id', $al->alliance_id)
					->where('diplomacy_id', $al->diplomacy_id)
					->delete();

				Models\AllianceDiplomacy::query()->where('alliance_id', $al->diplomacy_id)
					->where('diplomacy_id', $al->alliance_id)
					->delete();

				throw new RedirectException('/alliance/diplomacy', 'Отношение между вашими альянсами расторжено');
			} elseif ($request->query('edit', '') == 'suc') {
				$al = Models\AllianceDiplomacy::query()->where('id', (int) $request->query('id'))
					->where('alliance_id', $alliance->id)
					->first();

				if (!$al) {
					throw new RedirectException('/alliance/diplomacy', 'Ошибка ввода параметров');
				}

				Models\AllianceDiplomacy::query()->where('alliance_id', $al->alliance_id)
					->where('diplomacy_id', $al->diplomacy_id)
					->update(['status' => 1]);

				Models\AllianceDiplomacy::query()->where('alliance_id', $al->diplomacy_id)
					->where('diplomacy_id', $al->alliance_id)
					->update(['status' => 1]);

				throw new RedirectException('/alliance/diplomacy', 'Отношение между вашими альянсами подтверждено');
			}
		}

		$dp = DB::select("SELECT ad.*, a.name FROM alliances_diplomacies ad, alliances a WHERE a.id = ad.diplomacy_id AND ad.alliance_id = '" . $alliance->id . "'");

		foreach ($dp as $diplo) {
			if ($diplo->status == 0) {
				if ($diplo->primary == 1) {
					$parse['DMyQuery'][] = (array) $diplo;
				} else {
					$parse['DQuery'][] = (array) $diplo;
				}
			} else {
				$parse['DText'][] = (array) $diplo;
			}
		}

		$parse['a_list'] = [];

		$alliances = Alliance::query()->whereNot('id', $this->user->alliance_id)
			->where('members_count', '>', 0)
			->get();

		foreach ($alliances as $ally) {
			$parse['a_list'][] = $ally->only(['id', 'name', 'tag']);
		}

		return response()->state($parse);
	}

	public function exit(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			throw new ErrorException(__('alliance.Owner_cant_go_out'));
		}

		if ($request->query('yes')) {
			$alliance->deleteMember($this->user->id);

			$html = $this->MessageForm(__('alliance.Go_out_welldone'), "<br>", '/alliance/', __('alliance.Ok'));
		} else {
			$html = $this->MessageForm(__('alliance.Want_go_out'), "<br>", "/alliance/exit/yes/1/", "Подтвердить");
		}
	}

	public function members(Request $request)
	{
		$alliance = $this->getAlliance();

		$parse = [];

		if (Route::currentRouteAction() == 'admin') {
			$parse['admin'] = true;
		} else {
			if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST)) {
				throw new ErrorException(__('alliance.Denied_access'));
			}

			$parse['admin'] = false;
		}

		$sort1 = $request->query('sort1', 0);
		$sort2 = $request->query('sort2', 0);

		$rank = $request->query('rank', 0);

		if ($sort1 == 1) {
			$sort = 'u.username';
		} elseif ($sort1 == 2) {
			$sort = 'm.rank';
		} elseif ($sort1 == 3) {
			$sort = 's.total_points';
		} elseif ($sort1 == 4) {
			$sort = 'm.created_at';
		} elseif ($sort1 == 5 && $alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS)) {
			$sort = 'u.onlinetime';
		} else {
			$sort = 'u.id';
		}

		$members = DB::table('alliances_members', 'm')
			->select(['m.id', 'u.username', 'u.race', 'u.galaxy', 'u.system', 'u.planet', 'u.onlinetime', 'm.user_id', 'm.rank', 'm.created_at', 's.total_points'])
			->leftJoin('users as u', 'u.id', '=', 'm.user_id')
			->leftJoin('statistics as s', 's.user_id', '=', 'm.user_id')
			->where('s.stat_type', 1)
			->where('m.alliance_id', $alliance->id)
			->orderBy($sort, $sort2 == 1 ? 'desc' : 'asc')
			->get();

		$parse['members'] = [];

		foreach ($members as $member) {
			$item = [
				'id' => $member->user_id,
				'username' => $member->username,
				'race' => $member->race,
				'galaxy' => $member->galaxy,
				'system' => $member->system,
				'planet' => $member->planet,
				'points' => Format::number($member->total_points),
				'date' => $member->created_at ? Game::datezone("d.m.Y H:i", $member->created_at) : '-',
			];

			if (strtotime($member->onlinetime) + 60 * 10 >= time() && $alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = "<span class='positive'>" . __('alliance.On') . "</span>";
			} elseif (strtotime($member->onlinetime) + 60 * 20 >= time() && $alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = "<span class='neutral'>" . __('alliance.15_min') . "</span>";
			} elseif ($alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS)) {
				$hours = floor((time() - strtotime($member->onlinetime)) / 3600);

				$item['onlinetime'] = "<span class='negative'>" . __('alliance.Off') . " " . Format::time($hours * 3600) . "</span>";
			}

			if ($alliance->user_id == $member->user_id) {
				$item['range'] = ($alliance->owner_range == '') ? "Основатель" : $alliance->owner_range;
			} elseif ($member->rank && isset($alliance->ranks[$member->rank]['name'])) {
				$item['range'] = $alliance->ranks[$member->rank]['name'];
			} else {
				$item['range'] = __('alliance.Novate');
			}

			$parse['members'][] = $item;

			if ($rank == $member->user_id && $parse['admin']) {
				$r = [];
				$r['Rank_for'] = 'Установить ранг для ' . $member->username;
				$r['options'] = "<option value=\"0\">Новичок</option>";

				if (is_array($alliance->ranks) && !empty($alliance->ranks)) {
					foreach ($alliance->ranks as $a => $b) {
						$r['options'] .= "<option value=\"" . ($a + 1) . "\"";

						if ($member->rank == $a) {
							$r['options'] .= ' selected=selected';
						}

						$r['options'] .= ">" . $b['name'] . "</option>";
					}
				}

				$r['id'] = $member->user_id;

				$parse['members'][] = $r;
			}
		}

		if ($sort2 == 1) {
			$s = 2;
		} elseif ($sort2 == 2) {
			$s = 1;
		} else {
			$s = 1;
		}

		if (count($parse['members']) != $alliance->members_count) {
			$alliance->members_count = count($parse['memberslist']);
			$alliance->save();
		}

		$parse['s'] = $s;
		$parse['status'] = $alliance->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS);

		return response()->state($parse);
	}

	public function chat(Request $request)
	{
		if ($this->user->messages_ally != 0) {
			$this->user->messages_ally = 0;
			$this->user->update();
		}

		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(Alliance::CHAT_ACCESS)) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		if ($request->post('delete_type') && $alliance->user_id == $this->user->id) {
			$deleteType = $request->post('delete_type');

			if ($deleteType == 'all') {
				Models\AllianceChat::query()->where('alliance_id', $this->user->alliance_id)->delete();
			} elseif ($deleteType == 'marked' || $deleteType == 'unmarked') {
				$messages = $request->post('delete');

				if (is_array($messages)) {
					$messages = array_map('intval', $messages);

					if (count($messages)) {
						$query = Models\AllianceChat::query()
							->where('alliance_id', $this->user->alliance_id);

						if ($deleteType == 'unmarked') {
							$query->whereNotIn('id', $messages);
						} else {
							$query->whereIn('id', $messages);
						}

						$query->delete();
					}
				}
			}
		}

		if ($request->has('text') && $request->post('text', '') != '') {
			Models\AllianceChat::create([
				'alliance_id' 	=> $this->user->alliance_id,
				'user' 			=> $this->user->username,
				'user_id' 		=> $this->user->id,
				'message' 		=> Format::text($request->post('text')),
				'timestamp'		=> now(),
			]);

			User::query()->where('alliance_id', $this->user->alliance_id)
				->where('id', '!=', $this->user->id)
				->update(['messages_ally' => DB::raw('messages_ally + 1')]);

			throw new RedirectException('/alliance/chat', 'Сообщение отправлено');
		}

		$parse = [];
		$parse['items'] = [];

		$messagesCount = Models\AllianceChat::query()
			->where('alliance_id', $this->user->alliance_id)
			->count();

		$parse['pagination'] = [
			'total' => $messagesCount,
			'limit' => 10,
			'page' => (int) $request->query('p', 1)
		];

		if ($messagesCount > 0) {
			$messages = Models\AllianceChat::query()
				->where('alliance_id', $this->user->alliance_id)
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
					'text' => str_replace(["\r\n", "\n", "\r"], '', stripslashes($message->message)),
				];
			}
		}

		$parse['owner'] = $alliance->user_id == $this->user->id;
		$parse['parser'] = (bool) $this->user->getOption('bb_parser');

		return response()->state($parse);
	}

	public function info($id)
	{
		if ($id != '' && !is_numeric($id)) {
			$allyrow = Alliance::whereTag(addslashes(htmlspecialchars($id)))->first();
		} elseif ($id > 0 && is_numeric($id)) {
			$allyrow = Alliance::find((int) $id);
		} else {
			throw new ErrorException('Указанного альянса не существует в игре!');
		}

		if (!$allyrow) {
			throw new ErrorException('Указанного альянса не существует в игре!');
		}

		if (empty($allyrow->description)) {
			$allyrow->description = '[center]У этого альянса ещё нет описания[/center]';
		}

		$parse['id'] = $allyrow->id;
		$parse['member_scount'] = $allyrow->members_count;
		$parse['name'] = $allyrow->name;
		$parse['tag'] = $allyrow->tag;
		$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($allyrow->description));
		$parse['image'] = '';

		if ($allyrow->image > 0) {
			$file = Files::getById($allyrow->image);

			if ($file) {
				$parse['image'] = $file['src'];
			}
		}

		if ($allyrow->web != '' && !str_contains($allyrow->web, 'http')) {
			$allyrow->web = 'https://' . $allyrow->web;
		}

		$parse['web'] = $allyrow->web;
		$parse['request'] = ($this->user && $this->user->alliance_id == 0);

		return response()->state($parse);
	}

	public function make(Request $request)
	{
		$ally_request = AllianceRequest::query()->where('user_id', $this->user->id)->count();

		if ($this->user->alliance_id > 0 || $ally_request) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$tag = $request->post('tag', '');
		$name = $request->post('name', '');

		if ($tag == '') {
			throw new ErrorException(__('alliance.have_not_tag'));
		}
		if ($name == '') {
			throw new ErrorException(__('alliance.have_not_name'));
		}
		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new ErrorException("Абревиатура альянса содержит запрещённые символы");
		}
		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $name)) {
			throw new ErrorException("Название альянса содержит запрещённые символы");
		}

		$find = Alliance::query()->where('tag', addslashes($tag))->exists();

		if ($find) {
			throw new ErrorException(str_replace('%s', $tag, __('alliance.always_exist')));
		}

		$alliance = new Alliance();
		$alliance->name = addslashes($name);
		$alliance->tag = addslashes($tag);
		$alliance->user_id = $this->user->id;
		$alliance->ranks = [];

		if (!$alliance->save()) {
			throw new ErrorException('Произошла ошибка при создании альянса');
		}

		$member = new AllianceMember();
		$member->alliance_id = $alliance->id;
		$member->user_id = $this->user->id;

		if (!$member->save()) {
			throw new ErrorException('Произошла ошибка при создании альянса');
		}

		$this->user->alliance_id = $alliance->id;
		$this->user->alliance_name = $alliance->name;
		$this->user->update();

		throw new PageException(str_replace('%s', $alliance->tag, __('alliance.alliance_has_been_maked')), '/alliance/');
	}

	public function search(Request $request)
	{
		$ally_request = AllianceRequest::query()->where('user_id', $this->user->id)->count();

		if ($this->user->alliance_id > 0 || $ally_request) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$parse = [];
		$parse['result'] = [];

		$text = '';

		if ($request->post('searchtext') && $request->post('searchtext') != '') {
			$text = $request->post('searchtext');

			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $text)) {
				throw new RedirectException('/alliance/search', "Строка поиска содержит запрещённые символы");
			}

			$search = Alliance::query()->where('name', 'LIKE', '%' . $text . '%')
				->orWhere('tag', 'LIKE', '%' . $text . '%')
				->limit(30)->get();

			if ($search->count()) {
				foreach ($search as $s) {
					$entry = [];

					$entry['tag'] = "[<a href=\"" . URL::to('alliance/apply/allyid/' . $s->id . '/') . "\">" . $s->tag . "</a>]";
					$entry['name'] = $s->name;
					$entry['members'] = $s->members;

					$parse['result'][] = $entry;
				}
			}
		}

		$parse['searchtext'] = $text;

		return response()->state($parse);
	}

	public function apply(Request $request)
	{
		if ($this->user->alliance_id > 0) {
			throw new ErrorException(__('alliance.Denied_access'));
		}

		$allyid = $request->query('allyid', 0);

		if ($allyid <= 0) {
			throw new ErrorException(__('alliance.it_is_not_posible_to_apply'));
		}

		$allyrow = Alliance::find($allyid);

		if (!$allyrow) {
			throw new ErrorException("Альянса не существует!");
		}

		if ($allyrow->request_notallow != 0) {
			throw new ErrorException("Данный альянс является закрытым для вступлений новых членов");
		}

		if ($request->post('further')) {
			$existRequest = AllianceRequest::where('alliance_id', $allyrow->id)->where('user_id', $this->user->id)
				->exists();

			if ($existRequest) {
				throw new RedirectException('/alliance', 'Вы уже отсылали заявку на вступление в этот альянс!');
			}

			AllianceRequest::create([
				'alliance_id' => $allyrow->id,
				'user_id' => $this->user->id,
				'message' => strip_tags($request->post('text')),
			]);

			throw new RedirectException('/alliance', __('alliance.apply_registered'));
		}

		$parse = [];

		$parse['allyid'] = $allyrow->id;
		$parse['text_apply'] = ($allyrow->request) ? str_replace(["\r\n", "\n", "\r"], '', stripslashes($allyrow->request)) : '';
		$parse['tag'] = $allyrow->tag;

		return response()->state($parse);
	}

	public function stat(int $id)
	{
		$alliance = Alliance::find($id);

		if (!$alliance) {
			throw new PageException('Информация о данном альянсе не найдена');
		}

		$parse = [];
		$parse['name'] = $alliance->name;
		$parse['points'] = [];

		$items = Models\LogStat::query()->where('object_id', $alliance->id)
			->where('type', 2)
			->where('time', '>', now()->subDays(14))
			->orderBy('time')
			->get();

		foreach ($items as $item) {
			$parse['points'][] = [
				'date' => (int) $item->time,
				'rank' => [
					'tech' => (int) $item->tech_rank,
					'build' => (int) $item->build_rank,
					'defs' => (int) $item->defs_rank,
					'fleet' => (int) $item->fleet_rank,
					'total' => (int) $item->total_rank,
				],
				'point' => [
					'tech' => (int) $item->tech_points,
					'build' => (int) $item->build_points,
					'defs' => (int) $item->defs_points,
					'fleet' => (int) $item->fleet_points,
					'total' => (int) $item->total_points,
				]
			];
		}

		return response()->state($parse);
	}

	private function MessageForm($Title, $Message, $Goto = '', $Button = ' ok ', $TwoLines = false)
	{
		$Form = "<form action=\"" . URL::to(ltrim($Goto, '/')) . "\" method=\"post\">";
		$Form .= "<table width=\"100%\"><tr>";
		$Form .= "<td class=\"c\">" . $Title . "</td>";
		$Form .= "</tr><tr>";

		if ($TwoLines == true) {
			$Form .= "<th >" . $Message . "</th>";
			$Form .= "</tr><tr>";
			$Form .= "<th align=\"center\"><input type=\"submit\" value=\"" . $Button . "\"></th>";
		} else {
			$Form .= "<th>" . $Message . "<input type=\"submit\" value=\"" . $Button . "\"></th>";
		}

		$Form .= "</tr></table></form>";

		return $Form;
	}
}
