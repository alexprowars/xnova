<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Enums\PlanetType;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Http\Controllers\Controller;
use App\Models\FleetShortcut;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetShortcutController extends Controller
{
	public function index()
	{
		$items = [];

		foreach ($this->user->shortcuts as $item) {
			$items[] = [
				'id' => $item->id,
				'name' => $item->name,
				'galaxy' => $item->galaxy,
				'system' => $item->system,
				'planet' => $item->planet,
				'planet_type' => $item->planet_type,
			];
		}

		return Inertia::render('Fleet/Shortcuts/List', [
			'items' => $items,
		]);
	}

	public function create(Request $request)
	{
		$galaxy = $request->integer('g');
		$system = $request->integer('s');
		$planet = $request->integer('p');
		$planetType = $request->integer('t');

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));
		$planet = min(max($planet, 1), config('game.maxPlanetInSystem'));

		if (!in_array($planetType, array_column(PlanetType::cases(), 'value'))) {
			$planetType = 1;
		}

		return Inertia::render('Fleet/Shortcuts/Create', [
			'data' => [
				'galaxy' => $galaxy,
				'system' => $system,
				'planet' => $planet,
				'planet_type' => $planetType,
			],
		]);
	}

	public function store(Request $request)
	{
		$name = $request->post('name');

		if (empty($name) || !preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			$name = 'Планета';
		}

		$galaxy = (int) $request->post('galaxy', 0);
		$system = (int) $request->post('system', 0);
		$planet = (int) $request->post('planet', 0);
		$planetType = (int) $request->post('planet_type', 0);

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));
		$planet = min(max($planet, 1), config('game.maxPlanetInSystem'));

		if (!in_array($planetType, array_column(PlanetType::cases(), 'value'))) {
			$planetType = 1;
		}

		$this->user->shortcuts()->create([
			'name' => $name,
			'galaxy' => $galaxy,
			'system' => $system,
			'planet' => $planet,
			'planet_type' => $planetType,
		]);

		return to_route('fleet.shortcuts');
	}

	public function view(int $id)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new PageException('Данной ссылки не существует!');
		}

		return Inertia::render('Fleet/Shortcuts/Edit', [
			'data' => [
				'id' => $shortcut->id,
				'name' => $shortcut->name,
				'galaxy' => $shortcut->galaxy,
				'system' => $shortcut->system,
				'planet' => $shortcut->planet,
				'planet_type' => $shortcut->planet_type,
			]
		]);
	}

	public function update(int $id, Request $request)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new PageException('Данной ссылки не существует!');
		}

		$shortcut->name = strip_tags(str_replace(',', '', $request->post('name', '')));

		$galaxy = $request->integer('galaxy');
		$system = $request->integer('system');
		$planet = $request->integer('planet');
		$planetType = $request->integer('planet_type');

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));
		$planet = min(max($planet, 1), config('game.maxPlanetInSystem'));

		if (!in_array($planetType, array_column(PlanetType::cases(), 'value'))) {
			$planetType = 1;
		}

		$shortcut->galaxy = $galaxy;
		$shortcut->system = $system;
		$shortcut->planet = $planet;
		$shortcut->planet_type = $planetType;
		$shortcut->save();

		return to_route('fleet.shortcuts');
	}

	public function delete(int $id)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new PageException('Данной ссылки не существует!');
		}

		$shortcut->delete();

		return to_route('fleet.shortcuts');
	}
}
