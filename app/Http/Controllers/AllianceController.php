<?php

namespace App\Http\Controllers;

use App\Engine\Enums\AllianceAccess;
use App\Engine\Game;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Files;
use App\Format;
use App\Models;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class AllianceController extends Controller
{
	use AllianceControllerTrait;

	protected function noAlly(Request $request)
	{
		if ($request->post('bcancel') && $request->post('r_id')) {
			AllianceRequest::where('alliance_id', (int) $request->post('r_id'))
				->where('user_id', $this->user->id)
				->delete();

			throw new RedirectException('/alliance', 'Вы отозвали свою заявку на вступление в альянс');
		}

		$parse = [];

		$parse['requests'] = [];

		$requests = AllianceRequest::query()
			->where('user_id', $this->user->id)
			->with('alliance')
			->get();

		foreach ($requests as $item) {
			$parse['requests'][] = [$item->alliance_id, $item->alliance?->tag, $item->alliance?->name, $item->created_at?->utc()->toAtomString()];
		}

		$parse['alliances'] = [];

		$alliances = DB::table('alliances', 'a')
			->select(['a.id', 'a.tag', 'a.name', 'a.members_count as members', 's.total_points'])
			->leftJoin('statistics as s', 's.alliance_id', '=', 'a.id')
			->where('s.stat_type', 2)
			->where('s.stat_code', 1)
			->orderByDesc('s.total_points')
			->limit(15)
			->get();

		foreach ($alliances as $item) {
			$parse['alliances'][] = (array) $item;
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

		if ($alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			$parse['diplomacy'] = Models\AllianceDiplomacy::query()->where('diplomacy_id', $alliance->id)->where('status', 0)->count();
		}

		$parse['requests'] = 0;

		if ($alliance->user_id == $this->user->id || $alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			$parse['requests'] = Models\AllianceDiplomacy::query()->where('alliance_id', $alliance->id)->count();
		}

		$parse['alliance_admin'] = $alliance->canAccess(AllianceAccess::ADMIN_ACCESS);
		$parse['chat_access'] = $alliance->canAccess(AllianceAccess::CHAT_ACCESS);
		$parse['members_list'] = $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST);
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

	public function diplomacy(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$parse['DText'] = $parse['DMyQuery'] = $parse['DQuery'] = [];

		if ($request->query('edit')) {
			if ($request->query('edit', '') == "add") {
				$st = (int) $request->post('status', 0);
				$al = Alliance::find((int) $request->post('ally'));

				if (!$al) {
					throw new RedirectException('/alliance/diplomacy', 'Ошибка ввода параметров');
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
			throw new Exception(__('alliance.Owner_cant_go_out'));
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
			if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST)) {
				throw new Exception(__('alliance.Denied_access'));
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
		} elseif ($sort1 == 5 && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
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

			if (strtotime($member->onlinetime) + 60 * 10 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = "<span class='positive'>" . __('alliance.On') . "</span>";
			} elseif (strtotime($member->onlinetime) + 60 * 20 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = "<span class='neutral'>" . __('alliance.15_min') . "</span>";
			} elseif ($alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
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
		$parse['status'] = $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS);

		return response()->state($parse);
	}

	public function info($id)
	{
		if ($id != '' && !is_numeric($id)) {
			$allyrow = Alliance::whereTag(addslashes(htmlspecialchars($id)))->first();
		} elseif ($id > 0 && is_numeric($id)) {
			$allyrow = Alliance::find((int) $id);
		} else {
			throw new Exception('Указанного альянса не существует в игре!');
		}

		if (!$allyrow) {
			throw new Exception('Указанного альянса не существует в игре!');
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

	public function create(Request $request)
	{
		$ally_request = AllianceRequest::query()->whereBelongsTo($this->user)->count();

		if ($this->user->alliance_id > 0 || $ally_request) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$tag = $request->post('tag');
		$name = $request->post('name');

		if (empty($tag)) {
			throw new Exception(__('alliance.have_not_tag'));
		}
		if (empty($name)) {
			throw new Exception(__('alliance.have_not_name'));
		}
		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new Exception("Абревиатура альянса содержит запрещённые символы");
		}
		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $name)) {
			throw new Exception("Название альянса содержит запрещённые символы");
		}

		$find = Alliance::query()->where('tag', addslashes($tag))->exists();

		if ($find) {
			throw new Exception(str_replace('%s', $tag, __('alliance.always_exist')));
		}

		$alliance = new Alliance();
		$alliance->name = addslashes($name);
		$alliance->tag = addslashes($tag);
		$alliance->user_id = $this->user->id;
		$alliance->ranks = [];

		if (!$alliance->save()) {
			throw new Exception('Произошла ошибка при создании альянса');
		}

		$member = new AllianceMember();
		$member->alliance_id = $alliance->id;
		$member->user_id = $this->user->id;

		if (!$member->save()) {
			throw new Exception('Произошла ошибка при создании альянса');
		}

		$this->user->alliance_id = $alliance->id;
		$this->user->alliance_name = $alliance->name;
		$this->user->update();
	}

	public function search(Request $request)
	{
		$query = $request->post('query', '');

		if (!empty($query)) {
			return [];
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $query)) {
			throw new RedirectException('/alliance/search', "Строка поиска содержит запрещённые символы");
		}

		$items = [];

		$search = Alliance::query()->where('name', 'LIKE', '%' . $query . '%')
			->orWhere('tag', 'LIKE', '%' . $query . '%')
			->limit(30)->get();

		foreach ($search as $item) {
			$entry = $item->only(['name', 'members']);
			$entry['tag'] = "[<a href=\"" . URL::to('alliance/apply/' . $item->id) . "\">" . $item->tag . "</a>]";

			$items[] = $entry;
		}

		return $items;
	}

	public function apply(Request $request)
	{
		if ($this->user->alliance_id > 0) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$allyid = $request->query('allyid', 0);

		if ($allyid <= 0) {
			throw new Exception(__('alliance.it_is_not_posible_to_apply'));
		}

		$allyrow = Alliance::find($allyid);

		if (!$allyrow) {
			throw new Exception("Альянса не существует!");
		}

		if ($allyrow->request_notallow != 0) {
			throw new Exception("Данный альянс является закрытым для вступлений новых членов");
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
}
