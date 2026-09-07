<?php

namespace App\Http\Controllers\Alliance;

use App\Engine\Enums\AllianceAccess;
use App\Exceptions\Exception;
use App\Format;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AllianceMembersController extends Controller
{
	use AllianceControllerTrait;

	public function index(Request $request)
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$result = [];

		if (str_contains(Route::current()->uri(), '/admin')) {
			$result['admin'] = true;
		} else {
			$result['admin'] = false;
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

		$result['members'] = [];

		foreach ($members as $member) {
			$item = [
				'id' => $member->user_id,
				'username' => $member->user->username,
				'race' => $member->user->race,
				'rank' => $member->rank,
				'galaxy' => $member->user->galaxy,
				'system' => $member->user->system,
				'planet' => $member->user->planet,
				'points' => Format::number($member->user->statistics->total_points ?? 0),
				'date' => $member->created_at->utc()->toAtomString(),
				'online' => null,
			];

			if ($alliance->canAccess(AllianceAccess::CAN_WATCH_MEMBERLIST_STATUS)) {
				if (strtotime($member->user->onlinetime) + 60 * 10 >= time()) {
					$item['online'] = '<span class="positive">' . __('alliance.On') . '</span>';
				} elseif (strtotime($member->user->onlinetime) + 60 * 20 >= time()) {
					$item['online'] = '<span class="neutral">' . __('alliance.15_min') . '</span>';
				} else {
					$hours = (int) floor((time() - strtotime($member->user->onlinetime)) / 3600);

					$item['online'] = '<span class="negative">' . __('alliance.Off') . ' ' . Format::time($hours * 3600) . '</span>';
				}
			}

			if ($alliance->user_id == $member->user_id) {
				$item['range'] = empty($alliance->owner_range) ? 'Основатель' : $alliance->owner_range;
			} elseif ($member->rank !== null && isset($alliance->ranks[$member->rank]['name'])) {
				$item['range'] = $alliance->ranks[$member->rank]['name'];
			} else {
				$item['range'] = __('alliance.Novate');
			}

			$result['members'][] = $item;
		}

		if (count($result['members']) != $alliance->total_members) {
			$alliance->total_members = count($result['members']);
			$alliance->save();
		}

		$result['ranks'] = [];

		if (is_array($alliance->ranks) && !empty($alliance->ranks)) {
			foreach ($alliance->ranks as $a => $b) {
				$result['ranks'][] = [
					'id' => (int) $a,
					'name' => $b['name'],
				];
			}
		}

		$result['order'] = $order == 'desc' ? 'asc' : 'desc';

		return Inertia::render('Alliance/Members', $result);
	}

	public function kick(Request $request): void
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

		$alliance->deleteMember($user->id);
	}

	public function rank(Request $request): void
	{
		$alliance = $this->getAlliance();

		if ($alliance->user_id != $this->user->id && !$alliance->canAccess(AllianceAccess::CAN_EDIT_RIGHTS)) {
			throw new Exception(__('alliance.Denied_access'));
		}

		$id = (int) $request->input('id');
		$data = $request->validate([
			'rank' => ['present', 'nullable', 'integer', 'min:0'],
		]);
		$rank = $data['rank'] === null ? null : (int) $data['rank'];

		$user = User::find($id);

		if (!$user) {
			throw new Exception('Игрок не найден');
		}

		if ($user->id == $this->user->id) {
			throw new Exception(__('alliance.Denied_access'));
		}

		if ($user->id == $alliance->user_id) {
			$rank = null;
		}

		if (($rank === null || isset($alliance->ranks[$rank])) && $user->alliance_id == $alliance->id) {
			$alliance->members()->whereBelongsTo($user)
				->update(['rank' => $rank]);
		}
	}
}
