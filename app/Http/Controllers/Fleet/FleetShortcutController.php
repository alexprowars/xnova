<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Enums\PlanetType;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Http\Controllers\Controller;
use App\Models\FleetShortcut;
use Illuminate\Http\Request;

class FleetShortcutController extends Controller
{
	public function index()
	{
		$items = [];

		foreach ($this->user->shortcuts as $shortcut) {
			$items[] = [
				'id' => $shortcut->id,
				'name' => $shortcut->name,
				'galaxy' => $shortcut->galaxy,
				'system' => $shortcut->system,
				'planet' => $shortcut->planet,
				'planet_type' => $shortcut->planet_type,
			];
		}

		return response()->state([
			'items' => $items,
		]);
	}

	public function create(Request $request)
	{
		$galaxy = (int) $request->query('g', 0);
		$system = (int) $request->query('s', 0);
		$planet = (int) $request->query('p', 0);
		$planetType = (int) $request->query('t', 0);

		$galaxy = min(max($galaxy, 1), config('game.maxGalaxyInWorld'));
		$system = min(max($system, 1), config('game.maxSystemInGalaxy'));
		$planet = min(max($planet, 1), config('game.maxPlanetInSystem'));

		if (!in_array($planetType, array_column(PlanetType::cases(), 'value'))) {
			$planetType = 1;
		}

		return response()->state([
			'galaxy' => $galaxy,
			'system' => $system,
			'planet' => $planet,
			'planet_type' => $planetType,
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
	}

	public function view(int $id)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new RedirectException('/fleet/shortcut', 'Данной ссылки не существует!');
		}

		return response()->state([
			'id' => $shortcut->id,
			'name' => $shortcut->name,
			'galaxy' => $shortcut->galaxy,
			'system' => $shortcut->system,
			'planet' => $shortcut->planet,
			'planet_type' => $shortcut->planet_type,
		]);
	}

	public function update(int $id, Request $request)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new Exception('Данной ссылки не существует!');
		}

		$shortcut->name = strip_tags(str_replace(',', '', $request->post('name', '')));

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

		$shortcut->galaxy = $galaxy;
		$shortcut->system = $system;
		$shortcut->planet = $planet;
		$shortcut->planet_type = $planetType;
		$shortcut->save();
	}

	public function delete(int $id)
	{
		$shortcut = FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new Exception('Данной ссылки не существует!');
		}

		$shortcut->delete();
	}
}
