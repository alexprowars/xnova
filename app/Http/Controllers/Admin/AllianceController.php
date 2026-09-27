<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AllianceController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('alliances');

		return $this->listing(
			$request,
			'alliances',
			Alliance::query()->with('user')->withCount('members'),
			['id', 'name', 'tag', 'user', 'members_count', 'created_at'],
			array_map(fn($key) => Presentation::field($key), ['id', 'name', 'tag']),
			fn(Alliance $alliance) => [
				...Presentation::record($alliance, ['name', 'tag', 'members_count', 'created_at']),
				'user' => Presentation::userLink($alliance->user),
			],
		);
	}

	private function fields(?Alliance $alliance = null): array
	{
		$fields = [
			Presentation::field('name'),
			Presentation::field('tag'),
			Presentation::field('user_id', 'number'),
		];

		if ($alliance) {
			foreach (['description', 'text', 'request'] as $key) {
				$fields[] = Presentation::field($key, 'textarea');
			}

			$fields = [
				...$fields,
				Presentation::field('web', 'url'),
				Presentation::field('photo', 'file'),
				Presentation::field('public', 'checkbox'),
			];
		}

		return Presentation::fill($fields, $alliance);
	}

	public function create()
	{
		Access::authorize('alliances');

		return $this->form('alliances', $this->fields());
	}

	public function edit(Alliance $alliance)
	{
		Access::authorize('alliances');

		$ranks = [['value' => '', 'label' => __('admin.no_rank')]];

		foreach ($alliance->ranks ?? [] as $id => $rank) {
			$ranks[] = ['value' => $id, 'label' => $rank['name']];
		}

		$members = $alliance
			->members()
			->with('user')
			->orderBy('id')
			->paginate(20, ['*'], 'members_page')
			->withQueryString()
			->through(
				fn(AllianceMember $member) => [
					'id' => $member->id,
					'user' => Presentation::userLink($member->user),
					'rank' => $member->rank,
					'leader' => $member->user_id === $alliance->user_id,
					'created_at' => Presentation::value($member->created_at),
					'member_url' => '/admin/alliances/' . $alliance->id . '/members/' . $member->id,
					'ranks' => $ranks,
				],
			);

		return $this->form('alliances', $this->fields($alliance), $alliance->id, [
			'tables' => [
				Presentation::table(
					__('admin.members'),
					Presentation::columns(['id', 'user', 'rank', 'leader', 'created_at']),
					$members,
				),
			],
			'image' => $alliance->getFirstMediaUrl(),
		]);
	}

	public function store(Request $request)
	{
		return $this->update($request, new Alliance());
	}

	public function update(Request $request, Alliance $alliance)
	{
		Access::authorize('alliances');

		$rules = [
			'name' => 'required|string|max:32',
			'tag' => [
				'required',
				'string',
				'max:8',
				Rule::unique('alliances')->ignore($alliance->id)->whereNull('deleted_at'),
			],
			'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
		];

		if ($alliance->exists) {
			$rules = [
				...$rules,
				'description' => 'nullable|string|max:16000',
				'text' => 'nullable|string|max:16000',
				'request' => 'nullable|string|max:16000',
				'web' => 'nullable|url:http,https|max:255',
				'public' => 'required|boolean',
				'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
			];
		}

		$data = $request->validate($rules);

		DB::transaction(function () use ($alliance, $data, $request) {
			if ($alliance->exists) {
				$alliance->refreshForUpdate();
			}

			$leader = User::query()->lockForUpdate()->findOrFail($data['user_id']);

			if ($leader->alliance_id && $leader->alliance_id !== $alliance->id) {
				throw ValidationException::withMessages(['user_id' => __('admin.leader_in_alliance')]);
			}

			unset($data['photo']);

			$alliance->fill($data)->save();
			$alliance->members()->firstOrCreate(['user_id' => $leader->id]);

			$leader->update(['alliance_id' => $alliance->id, 'alliance_name' => $alliance->name]);

			User::query()
				->where('alliance_id', $alliance->id)
				->update(['alliance_name' => $alliance->name]);

			$alliance->update(['total_members' => $alliance->members()->count()]);

			if ($request->hasFile('photo')) {
				$alliance->addMediaFromRequest('photo')->toMediaCollection();
			}
		});

		return redirect('/admin/alliances/' . $alliance->id . '/edit')->with(
			'admin_notice',
			__('admin.saved'),
		);
	}

	public function member(Request $request, Alliance $alliance, int $member)
	{
		Access::authorize('alliances');

		$data = $request->validate([
			'action' => 'required|in:delete,leader,rank',
			'rank' => 'nullable|integer|min:0',
		]);

		DB::transaction(function () use ($alliance, $member, $data) {
			$alliance->refreshForUpdate();

			$member = $alliance->members()
				->lockForUpdate()
				->findOrFail($member);

			if ($data['action'] === 'delete') {
				if ($alliance->user_id === $member->user_id) {
					throw ValidationException::withMessages([
						'member' => __('admin.transfer_leader_first'),
					]);
				}

				$alliance->deleteMember($member->user_id);

				Cache::forget('app::planetlist_' . $member->user_id);
			} elseif ($data['action'] === 'leader') {
				$alliance->update(['user_id' => $member->user_id]);
			} else {
				$rank = $data['rank'] ?? null;

				if ($rank !== null && !isset($alliance->ranks[$rank])) {
					throw ValidationException::withMessages(['rank' => __('admin.invalid_rank')]);
				}

				$member->update(['rank' => $rank]);
			}
		});

		return back()->with('admin_notice', __('admin.saved'));
	}
}
