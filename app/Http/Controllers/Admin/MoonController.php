<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Engine\Coordinates;
use App\Engine\Enums\PlanetType;
use App\Facades\Galaxy;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MoonController extends PlanetController
{
	protected string $resource = 'moons';

	/** @return Builder<Planet> */
	protected function query(): Builder
	{
		return Planet::query()->where('planet_type', PlanetType::MOON)->with(['user', 'parentPlanet']);
	}

	protected function related(Planet $planet): ?Planet
	{
		return $planet->parentPlanet;
	}

	public function create(Request $request)
	{
		Access::authorize('moons');

		$fields = $this->fields();

		foreach ($fields as &$field) {
			$field['value'] =
				$field['key'] === 'name' ? __('main.sys_moon') : ($request->integer($field['key']) ?: null);
		}

		$fields[] = Presentation::field('diameter', 'number', [], 1);

		return $this->form('moons', $fields, extra: ['help' => __('admin.moon_size_help')]);
	}

	public function store(Request $request)
	{
		Access::authorize('moons');

		$data = $request->validate([
			...$this->coordinateRules(),
			'diameter' => 'required|integer|between:1,20',
		]);

		$moon = DB::transaction(function () use ($data) {
			$parent = Planet::query()
				->where('planet_type', PlanetType::PLANET)
				->where('galaxy', $data['galaxy'])
				->where('system', $data['system'])
				->where('planet', $data['planet'])
				->whereNull('destroyed_at')
				->lockForUpdate()
				->first();

			if (!$parent || $parent->moon_id || $parent->user_id != $data['user_id']) {
				throw ValidationException::withMessages(['planet' => __('admin.moon_parent_required')]);
			}

			$moon = Galaxy::createMoon(
				new Coordinates($data['galaxy'], $data['system'], $data['planet']),
				User::query()->findOrFail($data['user_id']),
				$data['diameter'],
			);

			if (!$moon) {
				throw ValidationException::withMessages(['planet' => __('admin.moon_creation_failed')]);
			}

			$moon->update(['name' => $data['name'], 'last_update' => now()]);

			return $moon;
		});

		return redirect('/admin/moons/' . $moon->id . '/edit')->with('admin_notice', __('admin.saved'));
	}

	protected function relocate(Planet $planet): void
	{
		$parent = Planet::query()
			->where('planet_type', PlanetType::PLANET)
			->where('galaxy', $planet->galaxy)
			->where('system', $planet->system)
			->where('planet', $planet->planet)
			->whereNull('destroyed_at')
			->lockForUpdate()
			->first();

		if (
			!$parent ||
			$parent->user_id != $planet->user_id ||
			($parent->moon_id && $parent->moon_id !== $planet->id)
		) {
			throw ValidationException::withMessages(['planet' => __('admin.moon_parent_required')]);
		}

		Planet::query()
			->where('moon_id', $planet->id)
			->update(['moon_id' => null]);

		$parent->update(['moon_id' => $planet->id]);
	}
}
