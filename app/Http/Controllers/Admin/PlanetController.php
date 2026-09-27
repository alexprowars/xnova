<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BuildingObject;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Facades\Vars;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlanetController extends AdminController
{
	protected string $resource = 'planets';

	/** @return Builder<Planet> */
	protected function query(): Builder
	{
		return Planet::query()
			->whereIn('planet_type', [PlanetType::PLANET, PlanetType::MILITARY_BASE])
			->with(['user', 'moon']);
	}

	protected function related(Planet $planet): ?Planet
	{
		return $planet->moon;
	}

	public function index(Request $request)
	{
		Access::authorize($this->resource);

		return $this->listing(
			$request,
			$this->resource,
			$this->query(),
			[
				'id',
				'user',
				'name',
				'coordinates',
				'planet_type',
				'created_at',
				'last_update',
				'last_active',
				'related_body',
				'destroyed_at',
			],
			array_map(fn($key) => Presentation::field($key), ['id', 'user_id', 'name', 'galaxy', 'system', 'planet']),
			function (Planet $planet) {
				return [
					...Presentation::record($planet, [
						'name',
						'planet_type',
						'created_at',
						'last_update',
						'last_active',
						'destroyed_at',
					]),
					'coordinates' => $planet->galaxy . ':' . $planet->system . ':' . $planet->planet,
					'user' => Presentation::userLink($planet->user),
					'related_body' => Presentation::bodyLink($this->related($planet)),
				];
			},
		);
	}

	protected function fields(?Planet $planet = null): array
	{
		$fields = array_map(fn($key) => Presentation::field($key, $key === 'name' ? 'text' : 'number'), [
			'name',
			'user_id',
			'galaxy',
			'system',
			'planet',
		]);

		if ($planet) {
			foreach ([
					'metal',
					'crystal',
					'deuterium',
					'debris_metal',
					'debris_crystal',
					'field_max',
					'diameter',
					'temp_min',
					'temp_max',
				] as $key
			) {
				$fields[] = Presentation::field($key, 'number');
			}
		}

		return Presentation::fill($fields, $planet);
	}

	public function create(Request $request)
	{
		Access::authorize($this->resource);

		return $this->form($this->resource, $this->fields());
	}

	protected function coordinateRules(): array
	{
		return [
			'name' => 'required|string|max:50',
			'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
			'galaxy' => 'required|integer|min:1|max:' . config('game.maxGalaxyInWorld'),
			'system' => 'required|integer|min:1|max:' . config('game.maxSystemInGalaxy'),
			'planet' => 'required|integer|min:1|max:' . config('game.maxPlanetInSystem'),
		];
	}

	public function store(Request $request)
	{
		Access::authorize('planets');

		$data = $request->validate($this->coordinateRules());

		$planet = DB::transaction(function () use ($data) {
			$user = User::query()
				->lockForUpdate()
				->findOrFail($data['user_id']);

			$planet = Galaxy::createPlanet(
				new Coordinates($data['galaxy'], $data['system'], $data['planet']),
				$user,
				$data['name'],
			);

			if (!$planet) {
				throw ValidationException::withMessages([
					'planet' => __('admin.planet_creation_failed'),
				]);
			}

			return $planet;
		});

		return redirect('/admin/planets/' . $planet->id . '/edit')->with('admin_notice', __('admin.saved'));
	}

	public function edit(Request $request, int $planet)
	{
		Access::authorize($this->resource);

		$planet = $this->query()
			->findOrFail($planet);

		$buildings = collect(Vars::getObjectsByType(ItemType::BUILDING))
			->filter(fn($object) => $object instanceof BuildingObject && $object->hasAllowedBuild($planet->planet_type))
			->map(
				fn(BuildingObject $object) => [
					'id' => $object->getId(),
					'name' => __('main.tech.' . $object->getId()),
					'level' => $planet->getLevel($object->getId()),
					'action_url' => '/admin/' . $this->resource . '/' . $planet->id . '/buildings/' . $object->getId(),
				],
			)
			->values();

		$tables = [
			Presentation::table(__('admin.buildings'), Presentation::columns(['id', 'name', 'level']), $buildings),
		];

		$queue = $planet->queue()
			->where('type', QueueType::BUILDING)
			->orderBy('id')
			->get()
			->map(fn($item) => [
				'id' => $item->id,
				'name' => __('main.tech.' . $item->object_id),
				'level' => $item->level,
				'operation' => $item->operation->value,
				'date' => Presentation::value($item->date),
				'date_end' => Presentation::value($item->date_end),
				'queue_url' => '/admin/' . $this->resource . '/' . $planet->id . '/queue/' . $item->id,
			]);

		$tables[] = Presentation::table(
			__('admin.queue'),
			Presentation::columns(['id', 'name', 'level', 'operation', 'date', 'date_end']),
			$queue,
		);

		$related = $this->related($planet);

		return $this->form($this->resource, $this->fields($planet), $planet->id, [
			'links' => [Presentation::userLink($planet->user), Presentation::bodyLink($related)],
			'tables' => $tables,
			'createMoon' =>
				$planet->planet_type === PlanetType::PLANET &&
				!$related &&
				$planet->user_id &&
				!$planet->destroyed_at &&
				$request->user()->can('moons')
					? '/admin/moons/create?' .
						http_build_query($planet->only(['user_id', 'galaxy', 'system', 'planet']))
					: null,
		]);
	}

	public function update(Request $request, int $planet)
	{
		Access::authorize($this->resource);

		$planet = $this->query()
			->findOrFail($planet);

		$rules = $this->coordinateRules();

		foreach (['metal', 'crystal', 'deuterium', 'debris_metal', 'debris_crystal', 'field_max', 'diameter'] as $key) {
			$rules[$key] = in_array($key, ['metal', 'crystal', 'deuterium'], true)
				? 'required|numeric|min:0|max:2147483647'
				: 'required|integer|min:0|max:2147483647';
		}

		$rules['field_max'] = 'required|integer|between:0,65535';
		$rules['diameter'] = 'required|integer|between:1,65535';
		$rules['temp_min'] = 'required|integer|between:-1000,1000';
		$rules['temp_max'] = 'required|integer|between:-1000,1000|gte:temp_min';

		$data = $request->validate($rules);

		DB::transaction(function () use ($planet, $data) {
			$planet->refreshForUpdate();

			$oldUserId = $planet->user_id;

			$planet->fill($data);

			if ($planet->isDirty(['galaxy', 'system', 'planet', 'user_id'])) {
				$original = $planet->getOriginal();

				$this->ensureNoFleets(new Coordinates($original['galaxy'], $original['system'], $original['planet']));

				if ($planet->queue()->exists()) {
					throw ValidationException::withMessages([
						'planet' => __('admin.queue_not_empty'),
					]);
				}

				$this->relocate($planet);
			}

			$planet->last_update = now();
			$planet->save();

			if ($oldUserId && $oldUserId != $planet->user_id) {
				$this->resetUserPlanet($oldUserId, [$planet->id, $planet->moon_id]);
			}

			User::query()->where('planet_id', $planet->id)->update($planet->only(['galaxy', 'system', 'planet']));

			Cache::forget('app::planetlist_' . $oldUserId);
			Cache::forget('app::planetlist_' . $planet->user_id);
		});

		return back()->with('admin_notice', __('admin.saved'));
	}

	protected function relocate(Planet $planet): void
	{
		$occupied = Planet::query()
			->where('galaxy', $planet->galaxy)
			->where('system', $planet->system)
			->where('planet', $planet->planet)
			->whereNotIn('id', array_filter([$planet->id, $planet->moon_id]))
			->exists();

		if ($occupied) {
			throw ValidationException::withMessages(['planet' => __('admin.occupied')]);
		}

		if ($planet->moon) {
			if ($planet->moon->queue()->exists()) {
				throw ValidationException::withMessages([
					'planet' => __('admin.queue_not_empty'),
				]);
			}

			$planet->moon->update($planet->only(['galaxy', 'system', 'planet', 'user_id']));
		}

		$planet->alliance_id = null;
	}

	protected function ensureNoFleets(Coordinates $coordinates): void
	{
		if (
			Fleet::query()
				->where(fn(Builder $query) => $query->coordinates(FleetDirection::START, $coordinates))
				->orWhere(fn(Builder $query) => $query->coordinates(FleetDirection::END, $coordinates))
				->exists()
		) {
			throw ValidationException::withMessages([
				'planet' => __('main.overview_planet_delete_fleet_in_transit'),
			]);
		}
	}

	private function resetUserPlanet(int $userId, array $ids): void
	{
		$user = User::query()->lockForUpdate()->find($userId);

		if (!$user) {
			return;
		}

		if (in_array($user->planet_id, $ids, true)) {
			$replacement = $user
				->planets()
				->where('planet_type', PlanetType::PLANET)
				->whereNull('destroyed_at')
				->whereNotIn('id', array_filter($ids))
				->first();

			if ($replacement) {
				$user->setMainPlanet($replacement);
			} else {
				$user->update([
					'planet_id' => null,
					'planet_current' => null,
					'galaxy' => 0,
					'system' => 0,
					'planet' => 0,
				]);
			}
		} elseif (in_array($user->planet_current, $ids, true)) {
			$user->update(['planet_current' => $user->planet_id]);
		}
	}

	public function destroy(Request $request, int $planet)
	{
		Access::authorize($this->resource);

		$request->validate(['immediate' => 'required|boolean']);

		$planet = $this->query()
			->findOrFail($planet);

		DB::transaction(function () use ($planet, $request) {
			$planet->refreshForUpdate();

			$this->ensureNoFleets(new Coordinates($planet->galaxy, $planet->system, $planet->planet));

			if (!$request->boolean('immediate') && $planet->user?->planet_id === $planet->id) {
				throw ValidationException::withMessages([
					'planet' => __('main.overview_deletemessage_wrong'),
				]);
			}

			$userId = $planet->user_id;
			$ids = [$planet->id];

			if ($planet->moon) {
				$ids[] = $planet->moon->id;
			}

			foreach (Planet::query()->whereIn('id', $ids)->lockForUpdate()->get() as $body) {
				$body->queue()->delete();
				$body->update([
					'destroyed_at' => $request->boolean('immediate') ? now() : now()->addDay(),
					'user_id' => null,
					'alliance_id' => null,
				]);

				if ($request->boolean('immediate')) {
					Planet::query()
						->where('moon_id', $body->id)
						->update(['moon_id' => null]);
					$body->delete();
				}
			}

			if ($userId) {
				$this->resetUserPlanet($userId, $ids);
				Cache::forget('app::planetlist_' . $userId);
			}
		});

		return redirect('/admin/' . $this->resource)->with('admin_notice', __('admin.saved'));
	}

	public function building(Request $request, int $planet, int $entity)
	{
		Access::authorize($this->resource);

		$planet = $this->query()->findOrFail($planet);
		$object = Vars::getItemObject($entity);

		abort_unless($object instanceof BuildingObject && $object->hasAllowedBuild($planet->planet_type), 404);

		$data = $request->validate(['level' => 'required|integer|between:0,1000']);

		DB::transaction(function () use ($planet, $entity, $data) {
			$planet->refreshForUpdate();
			$planet->updateAmount($entity, $data['level']);
			$planet->checkUsedFields();

			if ($planet->user) {
				QueueManager::recalculateForUser($planet->user);
			}
		});

		return back()->with('admin_notice', __('admin.saved'));
	}

	public function queue(Request $request, int $planet, int $queue)
	{
		Access::authorize($this->resource);

		$planet = $this->query()->findOrFail($planet);
		$data = $request->validate(['action' => 'required|in:delete,complete']);

		DB::transaction(function () use ($planet, $queue, $data) {
			$planet->refreshForUpdate();

			$item = $planet->queue()
				->where('type', QueueType::BUILDING)
				->lockForUpdate()
				->findOrFail($queue);

			$manager = new QueueManager($planet);
			$manager->loadQueue(true);

			if ($data['action'] === 'delete') {
				$manager->delete(Vars::getItemObject($item->object_id), $item->id);
			} else {
				$first = $planet->queue()
					->where('type', QueueType::BUILDING)
					->orderBy('id')
					->first();

				if ($first?->id !== $item->id) {
					throw ValidationException::withMessages([
						'queue' => __('admin.first_queue_only'),
					]);
				}

				$manager->nextBuildingQueue();

				$item->refresh();

				if (!$item->date) {
					throw ValidationException::withMessages([
						'queue' => __('admin.queue_not_started'),
					]);
				}

				$item->update([
					'date' => now()->subSeconds($item->getTime() + 1),
					'date_end' => now(),
				]);

				$manager->update();
			}

			$planet->checkUsedFields();
		});

		return back()->with('admin_notice', __('admin.saved'));
	}
}
