<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Engine\Game;
use App\Exceptions\Exception;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models\AllianceMember;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class AllianceMembersController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		$alliance = $this->getAlliance();

		$parse = [];

		if (str_contains(Route::current()->uri(), '/admin')) {
			$parse['admin'] = true;
		} else {
			if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST)) {
				throw new Exception(__('alliance.Denied_access'));
			}

			$parse['admin'] = false;
		}

		$sort  = $request->query('sort');
		$order = $request->query('order', 'asc');

		if ($sort == 'active' && !$alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
			$sort = '';
		}

		$sortSql = match ($sort) {
			'name' => 'user.username',
			'rank' => 'rank',
			'points' => 'user.statistics.total_points',
			'date' => 'created_at',
			'active' => 'user.onlinetime',
			default => 'id'
		};

		$members = $this->getAlliance()->members;
		$members->loadMissing(['user', 'user.statistics']);
		$members = $members->sortBy($sortSql, descending: $order == 'desc');

		$parse['members'] = [];

		foreach ($members as $member) {
			$item = [
				'id' => $member->user_id,
				'username' => $member->user->username,
				'race' => $member->user->race,
				'rank' => (int) $member->rank,
				'galaxy' => $member->user->galaxy,
				'system' => $member->user->system,
				'planet' => $member->user->planet,
				'points' => Format::number($member->user->statistics->total_points ?? 0),
				'date' => $member->created_at->utc()->toAtomString(),
			];

			if (strtotime($member->user->onlinetime) + 60 * 10 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['online'] = '<span class="positive">' . __('alliance.On') . '</span>';
			} elseif (strtotime($member->user->onlinetime) + 60 * 20 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['online'] = '<span class="neutral">' . __('alliance.15_min') . '</span>';
			} elseif ($alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$hours = floor((time() - strtotime($member->user->onlinetime)) / 3600);

				$item['online'] = '<span class="negative">' . __('alliance.Off') . ' ' . Format::time($hours * 3600) . '</span>';
			}

			if ($alliance->user_id == $member->user_id) {
				$item['range'] = empty($alliance->owner_range) ? 'Основатель' : $alliance->owner_range;
			} elseif ($member->rank && isset($alliance->ranks[$member->rank]['name'])) {
				$item['range'] = $alliance->ranks[$member->rank]['name'];
			} else {
				$item['range'] = __('alliance.Novate');
			}

			$parse['members'][] = $item;
		}

		if (count($parse['members']) != $alliance->members_count) {
			$alliance->members_count = count($parse['members']);
			$alliance->save();
		}

		$parse['ranks'] = [];

		if (is_array($alliance->ranks) && !empty($alliance->ranks)) {
			foreach ($alliance->ranks as $a => $b) {
				$parse['ranks'][] = [
					'id' => $a + 1,
					'name' => $b['name'],
				];
			}
		}

		$parse['order'] = $order == 'desc' ? 'asc' : 'desc';
		$parse['status'] = $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS);

		return $parse;
	}

	public function kick(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_KICK)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$kick = (int) $request->post('id', 0);

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_KICK) && $kick) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$user = User::findOne($kick);

		if (!$user || $user->alliance_id != $alliance->id || $user->id == $alliance->user_id) {
			throw new Exception(__('alliance.Denied_access'));
		}

		Planet::query()->whereBelongsTo($user)
			->where('alliance_id', $alliance->id)
			->update(['alliance_id' => null]);

		$user->alliance_id = null;
		$user->alliance_name = null;
		$user->save();

		AllianceMember::query()->whereBelongsTo($user)->delete();
	}

	public function rank(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_KICK)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$id = (int) $request->input('id');
		$rank = (int) $request->input('rank', 0);

		$user = User::find($id);

		if (!$user) {
			throw new Exception('Игрок не найден');
		}

		if ($user->id == $alliance->user_id) {
			$rank = 0;
		}

		if ((isset($alliance->ranks[$rank - 1]) || $rank == 0) && $user->alliance_id == $alliance->id) {
			$alliance->members()->whereBelongsTo($user)
				->update(['rank' => $rank]);
		}
	}
}
