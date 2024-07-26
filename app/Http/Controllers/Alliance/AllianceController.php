<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Files;
use App\Http\Controllers\Controller;
use App\Models;
use App\Models\Alliance;
use App\Models\AllianceDiplomacy;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AllianceController extends Controller
{
	use AllianceControllerTrait;

	protected function noAlly()
	{
		$parse = [
			'requests' => [],
			'alliances' => [],
		];

		$requests = AllianceRequest::query()
			->where('user_id', $this->user->id)
			->with('alliance')
			->get();

		foreach ($requests as $item) {
			$parse['requests'][] = [
				'id' => $item->id,
				'alliance_id' => $item->alliance_id,
				'tag' => $item->alliance?->tag,
				'name' => $item->alliance?->name,
				'date' => $item->created_at?->utc()->toAtomString(),
			];
		}

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

	public function index()
	{
		if (!$this->user->alliance_id) {
			return $this->noAlly();
		}

		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			$range = ($alliance->owner_range == '') ? 'Основатель' : $alliance->owner_range;
		} elseif ($alliance->member->rank != null && isset($alliance->ranks[$alliance->member->rank]['name'])) {
			$range = $alliance->ranks[$alliance->member->rank]['name'];
		} else {
			$range = __('alliance.member');
		}

		$parse = [];
		$parse['range'] = $range;
		$parse['diplomacy'] = 0;
		$parse['requests'] = 0;

		if ($alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			$parse['diplomacy'] = AllianceDiplomacy::query()->where('diplomacy_id', $alliance->id)->where('status', 0)->count();
		}

		if ($alliance->user_id == $this->user->id || $alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			$parse['requests'] = AllianceDiplomacy::query()->where('alliance_id', $alliance->id)->count();
		}

		$parse['access'] = $alliance->rights;
		$parse['owner'] = $alliance->user_id == $this->user->id;

		$parse['image'] = null;

		if ($alliance->image) {
			$image = Files::getById($alliance->image);

			if ($image) {
				$parse['image'] = $image['src'];
			}
		}

		$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->description));
		$parse['text'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->text));

		$parse['web'] = $alliance->web;

		if (!empty($parse['web']) && !str_contains($parse['web'], 'http')) {
			$parse['web'] = 'https://' . $parse['web'];
		}

		$parse['tag'] = $alliance->tag;
		$parse['members'] = $alliance->members_count;
		$parse['name'] = $alliance->name;
		$parse['id'] = $alliance->id;

		return response()->state($parse);
	}

	public function exit()
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			throw new Exception(__('alliance.Owner_cant_go_out'));
		}

		$alliance->deleteMember($this->user->id);
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

	public function join(int $id)
	{
		if ($this->user->alliance_id) {
			throw new RedirectException('/alliance', __('alliance.Denied_access'));
		}

		$alliance = Alliance::find($id);

		if (!$alliance) {
			throw new Exception('Альянса не существует!');
		}

		if ($alliance->request_notallow != 0) {
			throw new Exception('Данный альянс является закрытым для вступлений новых членов');
		}

		$parse = [];
		$parse['text'] = $alliance->request ? str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->request)) : '';
		$parse['tag'] = $alliance->tag;

		return response()->state($parse);
	}

	public function joinSend(int $id, Request $request)
	{
		if ($this->user->alliance_id) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$alliance = Alliance::find($id);

		if (!$alliance) {
			throw new Exception('Альянса не существует!');
		}

		if ($alliance->request_notallow != 0) {
			throw new Exception('Данный альянс является закрытым для вступлений новых членов');
		}

		$exist = $alliance->requests()->where('user_id', $this->user->id)
			->exists();

		if ($exist) {
			throw new Exception('Вы уже отсылали заявку на вступление в этот альянс!');
		}

		$alliance->requests()->create([
			'user_id' => $this->user->id,
			'message' => strip_tags($request->post('message')),
		]);
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
				],
			];
		}

		return response()->state($parse);
	}
}
