<?php

namespace App\Http\Controllers\Fleet;

use App\Exceptions\RedirectException;
use App\Http\Controllers\Controller;
use App\Models;
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
			'items' => $items
		]);
	}

	public function add(Request $request)
	{
		if ($request->isMethod('post')) {
			$name = $request->post('title', '');

			if ($name == '' || !preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
				$name = 'Планета';
			}

			$g = (int) $request->post('galaxy', 0);
			$s = (int) $request->post('system', 0);
			$p = (int) $request->post('planet', 0);
			$t = (int) $request->post('planet_type', 0);

			if ($g < 1 || $g > config('settings.maxGalaxyInWorld')) {
				$g = 1;
			}
			if ($s < 1 || $s > config('settings.maxSystemInGalaxy')) {
				$s = 1;
			}
			if ($p < 1 || $p > config('settings.maxPlanetInSystem')) {
				$p = 1;
			}
			if ($t != 1 && $t != 2 && $t != 3 && $t != 5) {
				$t = 1;
			}

			$this->user->shortcuts()->create([
				'name' => $name,
				'galaxy' => $g,
				'system' => $s,
				'planet' => $p,
				'planet_type' => $t,
			]);

			throw new RedirectException("/fleet/shortcut", "Ссылка на планету добавлена!");
		}

		$g = (int) $request->input('g', 0);
		$s = (int) $request->input('s', 0);
		$p = (int) $request->input('p', 0);
		$t = (int) $request->input('t', 0);

		if ($g < 1 || $g > config('settings.maxGalaxyInWorld')) {
			$g = 1;
		}
		if ($s < 1 || $s > config('settings.maxSystemInGalaxy')) {
			$s = 1;
		}
		if ($p < 1 || $p > config('settings.maxPlanetInSystem')) {
			$p = 1;
		}
		if ($t != 1 && $t != 2 && $t != 3 && $t != 5) {
			$t = 1;
		}

		return response()->state([
			'galaxy' => $g,
			'system' => $s,
			'planet' => $p,
			'planet_type' => $t,
		]);
	}

	public function view(Request $request, int $id)
	{
		$shortcut = Models\FleetShortcut::where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$shortcut) {
			throw new RedirectException("/fleet/shortcut", "Данной ссылки не существует!");
		}

		if ($request->isMethod('post')) {
			if ($request->has('delete')) {
				$shortcut->delete();

				throw new RedirectException("/fleet/shortcut", "Ссылка была успешно удалена!");
			} else {
				$shortcut->name = strip_tags(str_replace(',', '', $request->post('title', '')));

				$g = (int) $request->post('galaxy', 0);
				$s = (int) $request->post('system', 0);
				$p = (int) $request->post('planet', 0);
				$t = (int) $request->post('planet_type', 0);

				if ($g < 1 || $g > config('settings.maxGalaxyInWorld')) {
					$g = 1;
				}

				if ($s < 1 || $s > config('settings.maxSystemInGalaxy')) {
					$s = 1;
				}

				if ($p < 1 || $p > config('settings.maxPlanetInSystem')) {
					$p = 1;
				}

				if ($t != 1 && $t != 2 && $t != 3 && $t != 5) {
					$t = 1;
				}

				$shortcut->galaxy = $g;
				$shortcut->system = $s;
				$shortcut->planet = $p;
				$shortcut->planet_type = $t;
				$shortcut->save();

				throw new RedirectException("/fleet/shortcut", "Ссылка была обновлена!");
			}
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
}
