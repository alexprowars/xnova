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
			'name' => 'u.username',
			'rank' => 'm.rank',
			'points' => 's.total_points',
			'date' => 'm.created_at',
			'active' => 'u.onlinetime',
			default => 'u.id'
		};

		$members = DB::table('alliances_members', 'm')
			->select(['m.id', 'u.username', 'u.race', 'u.galaxy', 'u.system', 'u.planet', 'u.onlinetime', 'm.user_id', 'm.rank', 'm.created_at', 's.total_points'])
			->leftJoin('users as u', 'u.id', '=', 'm.user_id')
			->leftJoin('statistics as s', 's.user_id', '=', 'm.user_id')
			->where('s.stat_type', 1)
			->where('m.alliance_id', $alliance->id)
			->orderBy($sortSql, $order == 'desc' ? 'desc' : 'asc')
			->get();

		$parse['members'] = [];

		foreach ($members as $member) {
			$item = [
				'id' => $member->user_id,
				'username' => $member->username,
				'race' => $member->race,
				'rank' => (int) $member->rank,
				'galaxy' => $member->galaxy,
				'system' => $member->system,
				'planet' => $member->planet,
				'points' => Format::number($member->total_points),
				'date' => $member->created_at ? Game::datezone("d.m.Y H:i", $member->created_at) : '-',
			];

			if (strtotime($member->onlinetime) + 60 * 10 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = '<span class="positive">' . __('alliance.On') . '</span>';
			} elseif (strtotime($member->onlinetime) + 60 * 20 >= time() && $alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$item['onlinetime'] = '<span class="neutral">' . __('alliance.15_min') . '</span>';
			} elseif ($alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				$hours = floor((time() - strtotime($member->onlinetime)) / 3600);

				$item['onlinetime'] = '<span class="negative">' . __('alliance.Off') . ' ' . Format::time($hours * 3600) . '</span>';
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
			$alliance->members_count = count($parse['memberslist']);
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

		return response()->state($parse);
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

		$user = User::find($kick);

		if ($user || $user->alliance_id != $alliance->id || $user->id == $alliance->user_id) {
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
			$alliance->members()->where('user_id', $user->id)
				->update(['rank' => $rank]);
		}
	}
}
