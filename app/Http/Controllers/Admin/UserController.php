<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Engine\Enums\ItemType;
use App\Engine\QueueManager;
use App\Facades\Vars;
use App\Models\Blocked;
use App\Models\Fleet;
use App\Models\Friend;
use App\Models\LogsAttack;
use App\Models\LogsCredit;
use App\Models\LogsIp;
use App\Models\LogsTransfer;
use App\Models\Message;
use App\Models\Referal;
use App\Models\User;
use App\Services\UserService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('users');

		$columns = ['id', 'username', 'email', 'coordinates', 'race', 'sex', 'created_at', 'onlinetime', 'blocked_at'];

		return $this->listing(
			$request,
			'users',
			User::query(),
			$columns,
			array_map(fn($key) => Presentation::field($key), ['id', 'email', 'username']),
			function (User $user) {
				$row = Presentation::record($user, ['username', 'email', 'created_at', 'onlinetime', 'blocked_at']);
				$row['coordinates'] = $user->galaxy . ':' . $user->system . ':' . $user->planet;
				$row['race'] = __('main.race.' . $user->race);
				$row['sex'] = __('admin.sex_' . $user->sex);
				$row['is_blocked'] = $user->blocked_at?->isFuture() ?? false;
				return $row;
			},
		);
	}

	public function create()
	{
		Access::authorize('users');

		return $this->form('users', $this->fields());
	}

	private function fields(?User $user = null): array
	{
		$field = fn(string $key, string $type = 'text', array $options = []) => Presentation::field(
			$key,
			$type,
			$options,
		);

		$fields = [$field('username'), $field('email', 'email'), $field('password', 'password')];

		if ($user) {
			$fields = [
				...$fields,
				$field('race', 'select', Presentation::options(__('main.race'))),
				$field(
					'sex',
					'select',
					Presentation::options([
						0 => __('admin.sex_0'),
						1 => __('admin.sex_1'),
						2 => __('admin.sex_2'),
					]),
				),
				$field('locale', 'select', Presentation::options(['ru' => 'Русский', 'en' => 'English'])),
				$field('credits', 'number'),
				$field('avatar', 'number'),
				$field('about', 'textarea'),
			];

			if (auth()->user()->can('roles')) {
				$fields[] = $field(
					'roles',
					'checkboxes',
					Role::query()
						->where('guard_name', 'web')
						->orderBy('name')
						->get()
						->map(fn(Role $role) => ['value' => $role->id, 'label' => $role->name])
						->all(),
				);
			}

			foreach (Vars::getOfficiers() as $code) {
				$fields[] = $field('officier_' . $code, 'datetime-local');
			}
		}

		return Presentation::fill($fields, $user);
	}

	public function store(Request $request)
	{
		Access::authorize('users');

		$data = $request->validate([
			'username' => 'required|string|max:50',
			'email' => 'required|email|max:50|unique:users,email',
			'password' => 'required|string|min:8|max:100',
		]);

		$user = UserService::creation([
			'name' => $data['username'],
			'email' => $data['email'],
			'password' => $data['password'],
		]);

		return redirect('/admin/users/' . $user->id . '/edit')->with('admin_notice', __('admin.saved'));
	}

	public function edit(Request $request, User $user)
	{
		Access::authorize('users');

		$links = [];

		if ($user->alliance && $request->user()->can('alliances')) {
			$links[] = Presentation::link(
				__('admin.alliances') . ': ' . $user->alliance->name,
				'/admin/alliances/' . $user->alliance_id . '/edit',
			);
		}

		foreach (['messages', 'fleets'] as $resource) {
			if ($request->user()->can($resource)) {
				$count = ($resource === 'messages' ? Message::query() : Fleet::query())
					->where('user_id', $user->id)
					->count();

				$links[] = Presentation::link(
					__('admin.' . $resource) . ' (' . $count . ')',
					'/admin/' . $resource . '?user_id=' . $user->id,
				);
			}
		}

		$links[] = Presentation::link(__('admin.logs'), '/admin/users/' . $user->id . '/logs');

		$tables = [];

		$planets = $user
			->planets()
			->with(['user', 'moon'])
			->orderBy('id')
			->get()
			->map(function ($planet) {
				$row = Presentation::record($planet, ['id', 'planet_type']);
				$row['name'] = Presentation::bodyLink($planet);
				$row['coordinates'] = $planet->galaxy . ':' . $planet->system . ':' . $planet->planet;
				return $row;
			});

		$tables[] = Presentation::table(
			__('admin.planets'),
			Presentation::columns(['id', 'name', 'coordinates', 'planet_type']),
			$planets,
		);

		$technologies = collect(Vars::getItemsByType(ItemType::TECH))
			->map(
				fn(int $id) => [
					'id' => $id,
					'name' => __('main.tech.' . $id),
					'level' => $user->getTechLevel($id),
					'action_url' => '/admin/users/' . $user->id . '/research/' . $id,
				],
			)
			->values();

		$tables[] = Presentation::table(
			__('admin.research'),
			Presentation::columns(['id', 'name', 'level']),
			$technologies,
		);

		return Inertia::render('Admin/Edit', [
			'resource' => 'users',
			'id' => $user->id,
			'title' => $user->username . ' #' . $user->id,
			'fields' => $this->fields($user),
			'url' => '/admin/users/' . $user->id,
			'links' => $links,
			'tables' => $tables,
			'status' => [
				'blocked_at' => Presentation::value($user->blocked_at),
				'is_blocked' => $user->blocked_at?->isFuture() ?? false,
				'vacation' => Presentation::value($user->vacation),
				'delete_time' => Presentation::value($user->delete_time),
			],
		]);
	}

	public function update(Request $request, User $user)
	{
		Access::authorize('users');

		$rules = [
			'username' => 'required|string|max:50',
			'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($user->id)],
			'password' => 'nullable|string|min:8|max:100',
			'race' => 'required|integer|between:0,4',
			'sex' => 'required|integer|between:0,2',
			'locale' => 'required|in:ru,en',
			'credits' => 'required|integer|min:0|max:2147483647',
			'avatar' => 'required|integer|between:0,8',
			'about' => 'nullable|string|max:10000',
		];

		foreach (Vars::getOfficiers() as $code) {
			$rules['officier_' . $code] = 'nullable|date';
		}

		if ($request->user()->can('roles')) {
			$rules['roles'] = 'present|array';
			$rules['roles.*'] = ['integer', 'distinct', Rule::exists('roles', 'id')->where('guard_name', 'web')];
		}

		$data = $request->validate($rules);

		DB::transaction(function () use ($data, $user) {
			$user->refreshForUpdate();

			if (isset($data['roles'])) {
				$user->syncRoles(Role::query()->whereIn('id', $data['roles'])->get());
				unset($data['roles']);
			}

			if (!empty($data['password'])) {
				$data['password'] = Hash::make($data['password']);
			} else {
				unset($data['password']);
			}

			$difference = $data['credits'] - $user->credits;

			$user->fill($data)->save();

			if ($difference != 0) {
				LogsCredit::create(['user_id' => $user->id, 'amount' => $difference, 'type' => 0]);
			}
		});

		return back()->with('admin_notice', __('admin.saved'));
	}

	public function research(Request $request, User $user, int $entity)
	{
		Access::authorize('users');

		abort_unless(in_array($entity, Vars::getItemsByType(ItemType::TECH), true), 404);

		$data = $request->validate(['level' => 'required|integer|between:0,1000']);

		DB::transaction(function () use ($user, $entity, $data) {
			$user->refreshForUpdate();
			$user->setTech($entity, $data['level']);

			QueueManager::recalculateForUser($user);
		});

		return back()->with('admin_notice', __('admin.saved'));
	}

	public function action(Request $request, User $user)
	{
		$data = $request->validate([
			'action' => 'required|in:ban,unban,vacation,delete',
			'until' => 'required_if:action,ban,vacation|nullable|date|after:now',
			'reason' => 'nullable|string|max:250',
			'immediate' => 'sometimes|boolean',
		]);

		Access::authorize(
			match ($data['action']) {
				'ban' => 'users-block',
				'unban' => 'users-unblock',
				default => 'users',
			},
		);

		if (in_array($data['action'], ['ban', 'delete'], true)) {
			abort_if($user->id === 1 || $user->id === $request->user()->id, 403);
		}

		DB::transaction(function () use ($user, $data) {
			$user->refreshForUpdate();
			switch ($data['action']) {
				case 'ban':
					Blocked::create([
						'user_id' => $user->id,
						'reason' => $data['reason'] ?? '',
						'longer' => $data['until'],
						'author_id' => auth()->id(),
					]);

					$user->update(['blocked_at' => $data['until']]);

					break;
				case 'unban':
					Blocked::query()->whereBelongsTo($user)->delete();

					$user->blocked_at = null;

					if ($user->vacation?->timestamp === 0) {
						$user->vacation = null;
					}

					$user->save();

					break;
				case 'vacation':
					$startedAt = CarbonImmutable::now();

					$ids = [4, 12, 212];

					foreach (Vars::getResources() as $resource) {
						$ids[] = Vars::getIdByName($resource . '_mine');
					}

					foreach ($user->planets()->lockForUpdate()->get() as $planet) {
						$planet->setRelation('user', $user);
						$planet->getProduction($startedAt)->update();

						foreach ($planet->entities->whereIn('entity_id', $ids) as $entity) {
							$entity->setFactor(0);
							$entity->save();
						}
					}

					$user->update(['vacation' => $data['until']]);

					break;
				case 'delete':
					if ($data['immediate'] ?? false) {
						$user->delete();
					} else {
						$user->update(['delete_time' => now()->addDays(7)]);
					}
			}
		});

		return $user->trashed()
			? redirect('/admin/users')->with('admin_notice', __('admin.saved'))
			: back()->with('admin_notice', __('admin.saved'));
	}

	public function logs(Request $request, User $user)
	{
		Access::authorize('users');

		$type = $request->string('type', 'transfers')
			->toString();

		$query = match ($type) {
			'transfers' => LogsTransfer::query()->where(function ($query) use ($user) {
				$query->where('user_id', $user->id)->orWhere('target_id', $user->id);
			}),
			'ips' => LogsIp::query()->where('user_id', $user->id),
			'credits' => LogsCredit::query()->where('user_id', $user->id),
			'attacks' => LogsAttack::query()->where('user_id', $user->id),
			'referrals' => Referal::query()->with('referal')->where('user_id', $user->id),
			'friends' => Friend::query()
				->with(['user', 'friend'])
				->where('active', true)
				->where(function ($query) use ($user) {
					$query->where('user_id', $user->id)->orWhere('friend_id', $user->id);
				}),
			default => abort(404),
		};

		$keys = match ($type) {
			'transfers' => ['id', 'user_id', 'target_id', 'data', 'created_at'],
			'ips' => ['id', 'ip', 'created_at'],
			'credits' => ['id', 'amount', 'type', 'created_at'],
			'attacks' => ['id', 'planet_start', 'planet_end', 'fleet', 'battle_log', 'created_at'],
			'referrals' => ['id', 'referal_id', 'date'],
			'friends' => ['id', 'friend_id', 'created_at'],
		};

		$rows = $query
			->orderByDesc('id')
			->paginate(20)
			->withQueryString()
			->through(function ($record) use ($keys, $type, $user) {
				$row = [];

				foreach ($keys as $key) {
					$row[$key] = Presentation::value($record->$key);
				}

				if ($type === 'ips') {
					$row['ip'] = long2ip($record->ip);
				} elseif ($type === 'referrals') {
					$row['referal_id'] = Presentation::userLink($record->referal);
				} elseif ($type === 'friends') {
					$row['friend_id'] = Presentation::userLink(
						$record->user_id === $user->id ? $record->friend : $record->user,
					);
				}

				return $row;
			});

		return Inertia::render('Admin/Logs', [
			'title' => __('admin.logs') . ' · ' . $user->username,
			'id' => $user->id,
			'type' => $type,
			'table' => Presentation::table(__('admin.' . $type), Presentation::columns($keys), $rows),
		]);
	}
}
