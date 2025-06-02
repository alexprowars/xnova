<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AllianceRanksController extends Controller
{
	use AllianceControllerTrait;

	public function index()
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$parse['alliance'] = $alliance->only(['id', 'user_id']);
		$parse['items'] = [];

		foreach ($alliance->ranks as $a => $rank) {
			$rights = [];

			foreach (AllianceAccess::cases() as $case) {
				$rights[$case->value] = (bool) ($rank[$case->value] ?: false);
			}

			$parse['items'][] = [
				'id' => $a,
				'name' => $rank['name'],
				'rights' => $rights,
			];
		}

		return $parse;
	}

	public function create(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$ranks = $alliance->ranks;

		$rank = [
			'name' => strip_tags($request->post('name')),
		];

		foreach (AllianceAccess::cases() as $case) {
			$rank[$case->value] = 0;
		}

		$ranks[] = $rank;

		$alliance->ranks = $ranks;
		$alliance->save();
	}

	public function update(Request $request)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$rights = Arr::wrap($request->post('rigths', []));

		if (empty($rights)) {
			throw new Exception('Ошибка в передаче параметров');
		}

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
	}

	public function remove(int $id)
	{
		$alliance = $this->getAlliance();

		if (!$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS) && !$this->user->isAdmin()) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$ranks = $alliance->ranks;

		if (isset($ranks[$id])) {
			unset($ranks[$id]);

			$alliance->ranks = $ranks;
			$alliance->save();
		}
	}
}
