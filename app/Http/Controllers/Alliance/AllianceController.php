<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Models;
use App\Models\Alliance;
use App\Models\AllianceDiplomacy;
use App\Models\AllianceMember;
use App\Models\AllianceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AllianceController extends Controller
{
	use AllianceControllerTrait;

	protected function noAlly()
	{
		$result = [
			'requests' => [],
			'alliances' => [],
		];

		$requests = AllianceRequest::query()
			->with('alliance')
			->whereBelongsTo($this->user)
			->get();

		foreach ($requests as $item) {
			$result['requests'][] = [
				'id' => $item->id,
				'alliance_id' => $item->alliance_id,
				'tag' => $item->alliance?->tag,
				'name' => $item->alliance?->name,
				'date' => $item->created_at?->utc()->toAtomString(),
			];
		}

		$alliances = DB::table('alliances', 'a')
			->select(['a.id', 'a.tag', 'a.name', 'a.total_members as members', 's.total_points'])
			->leftJoin('statistics as s', 's.alliance_id', '=', 'a.id')
			->where('s.stat_type', 2)
			->where('s.stat_code', 1)
			->orderByDesc('s.total_points')
			->limit(15)
			->get();

		foreach ($alliances as $item) {
			$result['alliances'][] = (array) $item;
		}

		return Inertia::render('Alliance/Empty', $result);
	}

	public function index()
	{
		if (!$this->user->alliance_id) {
			return $this->noAlly();
		}

		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			$range = ($alliance->owner_rank == '') ? 'Основатель' : $alliance->owner_rank;
		} elseif ($alliance->member->rank != null && isset($alliance->ranks[$alliance->member->rank]['name'])) {
			$range = $alliance->ranks[$alliance->member->rank]['name'];
		} else {
			$range = __('alliance.member');
		}

		$result = [];
		$result['range'] = $range;
		$result['diplomacy'] = 0;
		$result['requests'] = 0;

		if ($alliance->canAccess(AllianceAccess::DIPLOMACY_ACCESS)) {
			$result['diplomacy'] = AllianceDiplomacy::query()->where('diplomacy_id', $alliance->id)->where('status', 0)->count();
		}

		if ($alliance->user_id == $this->user->id || $alliance->canAccess(AllianceAccess::REQUEST_ACCESS)) {
			$result['requests'] = AllianceDiplomacy::query()->where('alliance_id', $alliance->id)->count();
		}

		$result['access'] = $alliance->rights;
		$result['owner'] = $alliance->user_id == $this->user->id;
		$result['image'] = $alliance->getFirstMediaUrl(conversionName: 'thumb') ?: null;

		$result['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->description));
		$result['text'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->text));

		$result['web'] = $alliance->web;

		if (!empty($result['web']) && !str_contains($result['web'], 'http')) {
			$result['web'] = 'https://' . $result['web'];
		}

		$result['tag'] = $alliance->tag;
		$result['members'] = $alliance->total_members;
		$result['name'] = $alliance->name;
		$result['id'] = $alliance->id;

		return Inertia::render('Alliance/Main', [
			'data' => $result,
		]);
	}

	public function exit(): void
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id == $this->user->id) {
			throw new Exception(__('alliance.Owner_cant_go_out'));
		}

		$alliance->deleteMember($this->user->id);
	}

	public function info(int | string $id)
	{
		if ($id != '' && !is_numeric($id)) {
			$alliance = Alliance::whereTag(addslashes(htmlspecialchars($id)))->first();
		} elseif ($id > 0 && is_numeric($id)) {
			$alliance = Alliance::find((int) $id);
		} else {
			throw new Exception('Указанного альянса не существует в игре!');
		}

		if (!$alliance) {
			throw new Exception('Указанного альянса не существует в игре!');
		}

		if (empty($alliance->description)) {
			$alliance->description = '[center]У этого альянса ещё нет описания[/center]';
		}

		$parse = [];
		$parse['id'] = $alliance->id;
		$parse['total_members'] = $alliance->total_members;
		$parse['name'] = $alliance->name;
		$parse['tag'] = $alliance->tag;
		$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->description));
		$parse['image'] = $alliance->getFirstMediaUrl(conversionName: 'thumb') ?: null;

		if ($alliance->web != '' && !str_contains($alliance->web, 'http')) {
			$alliance->web = 'https://' . $alliance->web;
		}

		$parse['web'] = $alliance->web;
		$parse['request'] = $this->user && $this->user->alliance_id == 0;

		return Inertia::render('Alliance/Info', [
			'data' => $parse,
		]);
	}

	public function createPage()
	{
		return Inertia::render('Alliance/Create');
	}

	public function create(Request $request)
	{
		$ally_request = AllianceRequest::query()->whereBelongsTo($this->user)->count();

		if ($this->user->alliance_id > 0 || $ally_request) {
			throw new PageException(__('alliance.Denied_access'));
		}

		$tag = $request->post('tag');
		$name = $request->post('name');

		if (empty($tag)) {
			throw new PageException(__('alliance.have_not_tag'));
		}

		if (empty($name)) {
			throw new PageException(__('alliance.have_not_name'));
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $tag)) {
			throw new PageException("Абревиатура альянса содержит запрещённые символы");
		}

		if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $name)) {
			throw new PageException("Название альянса содержит запрещённые символы");
		}

		$find = Alliance::query()
			->where('tag', addslashes($tag))
			->exists();

		if ($find) {
			throw new PageException(str_replace('%s', $tag, __('alliance.always_exist')));
		}

		$alliance = new Alliance();
		$alliance->name = addslashes($name);
		$alliance->tag = addslashes($tag);
		$alliance->user_id = $this->user->id;
		$alliance->ranks = [];

		if (!$alliance->save()) {
			throw new PageException('Произошла ошибка при создании альянса');
		}

		$member = new AllianceMember();
		$member->alliance_id = $alliance->id;
		$member->user_id = $this->user->id;

		if (!$member->save()) {
			throw new PageException('Произошла ошибка при создании альянса');
		}

		$this->user->alliance()->associate($alliance);
		$this->user->alliance_name = $alliance->name;
		$this->user->save();

		return to_route('alliance');
	}

	public function search(Request $request)
	{
		$query = $request->post('query', '');
		$items = [];

		if (!empty($query)) {
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u', $query)) {
				throw new PageException('Строка поиска содержит запрещённые символы');
			}

			$search = Alliance::query()->where('name', 'LIKE', '%' . $query . '%')
				->orWhere('tag', 'LIKE', '%' . $query . '%')
				->limit(30)->get();

			foreach ($search as $item) {
				$items[] = $item->only(['id', 'name', 'tag', 'members']);
			}
		}

		return Inertia::render('Alliance/Search', [
			'items' => $items,
		]);
	}

	public function join(int $id)
	{
		if ($this->user->alliance_id) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$alliance = Alliance::find($id);

		if (!$alliance) {
			throw new Exception('Альянса не существует!');
		}

		if (!$alliance->public) {
			throw new Exception('Данный альянс является закрытым для вступлений новых членов');
		}

		$text = str_replace(["\r\n", "\n", "\r"], '', stripslashes($alliance->request ?? ''));

		return Inertia::render('Alliance/Join', [
			'data' => [
				'id' => $alliance->id,
				'tag' => $alliance->tag,
				'text' => $text,
			],
		]);
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

		if (!$alliance->public) {
			throw new Exception('Данный альянс является закрытым для вступлений новых членов');
		}

		$exist = $alliance->requests()->whereBelongsTo($this->user)
			->exists();

		if ($exist) {
			throw new Exception('Вы уже отсылали заявку на вступление в этот альянс!');
		}

		$alliance->requests()->create([
			'user_id' => $this->user->id,
			'message' => strip_tags($request->post('message')),
		]);

		return to_route('alliance');
	}

	public function stat(int $id)
	{
		$alliance = Alliance::findOne($id);

		if (!$alliance) {
			throw new Exception('Информация о данном альянсе не найдена');
		}

		$result = [
			'name' => $alliance->name,
			'points' => [],
		];

		$items = Models\LogsStat::query()->where('object_id', $alliance->id)
			->where('type', 2)
			->where('date', '>', now()->subDays(14))
			->orderBy('date')
			->get();

		foreach ($items as $item) {
			$result['points'][] = [
				'date' => $item->date->utc()->toAtomString(),
				'rank' => [
					'tech' => $item->tech_rank,
					'build' => $item->build_rank,
					'defs' => $item->defs_rank,
					'fleet' => $item->fleet_rank,
					'total' => $item->total_rank,
				],
				'point' => [
					'tech' => $item->tech_points,
					'build' => $item->build_points,
					'defs' => $item->defs_points,
					'fleet' => $item->fleet_points,
					'total' => $item->total_points,
				],
			];
		}

		return Inertia::render('Alliance/Stats', [
			'data' => $result,
		]);
	}
}
