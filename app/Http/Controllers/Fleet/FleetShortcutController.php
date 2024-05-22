<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers\Fleet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Controller;
use App\Models;
use App\Exceptions\RedirectException;

class FleetShortcutController extends Controller
{
	public function index()
	{
		$inf = Models\UserDetail::query()->find($this->user->id, ['fleet_shortcut']);

		$links = [];

		if ($inf->fleet_shortcut) {
			$scarray = explode("\r\n", $inf->fleet_shortcut);

			foreach ($scarray as $a => $b) {
				if ($b != '') {
					$c = explode(',', $b);

					$type = '';

					if ($c[4] == 2) {
						$type = " (E)";
					} elseif ($c[4] == 3) {
						$type = " (L)";
					} elseif ($c[4] == 5) {
						$type = " (B)";
					}

					$links[] =
					[
						'name' => $c[0],
						'galaxy' => $c[1],
						'system' => $c[2],
						'planet' => $c[3],
						'type' => $type
					];
				}
			}
		}

		return [
			'items' => $links
		];
	}

	public function add(Request $request)
	{
		$inf = Models\UserDetail::query()->find($this->user->id, ['fleet_shortcut']);

		if ($request->isMethod('post')) {
			$name = $request->post('title', '');

			if ($name == '' || !preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
				$name = 'Планета';
			}

			$g = (int) $request->post('galaxy', 0);
			$s = (int) $request->post('system', 0);
			$p = (int) $request->post('planet', 0);
			$t = (int) $request->post('planet_type', 0);

			if ($g < 1 || $g > config('game.maxGalaxyInWorld')) {
				$g = 1;
			}
			if ($s < 1 || $s > config('game.maxSystemInGalaxy')) {
				$s = 1;
			}
			if ($p < 1 || $p > config('game.maxPlanetInSystem')) {
				$p = 1;
			}
			if ($t != 1 && $t != 2 && $t != 3 && $t != 5) {
				$t = 1;
			}

			$inf['fleet_shortcut'] .= strip_tags(str_replace(',', '', $name)) . "," . $g . "," . $s . "," . $p . "," . $t . "\r\n";

			Models\UserDetail::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

			if (Session::has('fleet_shortcut')) {
				Session::remove('fleet_shortcut');
			}

			throw new RedirectException("Ссылка на планету добавлена!", "/fleet/shortcut/");
		}

		$g = (int) $request->input('g', 0);
		$s = (int) $request->input('s', 0);
		$p = (int) $request->input('p', 0);
		$t = (int) $request->input('t', 0);

		if ($g < 1 || $g > config('game.maxGalaxyInWorld')) {
			$g = 1;
		}
		if ($s < 1 || $s > config('game.maxSystemInGalaxy')) {
			$s = 1;
		}
		if ($p < 1 || $p > config('game.maxPlanetInSystem')) {
			$p = 1;
		}
		if ($t != 1 && $t != 2 && $t != 3 && $t != 5) {
			$t = 1;
		}

		return [
			'id' => -1,
			'name' => '',
			'galaxy' => $g,
			'system' => $s,
			'planet' => $p,
			'type' => $t,
		];
	}

	public function view(Request $request, int $id)
	{
		$inf = Models\UserDetail::query()->find($this->user->id, ['fleet_shortcut']);

		if ($request->isMethod('post')) {
			$scarray = explode("\r\n", $inf['fleet_shortcut']);

			if (!isset($scarray[$id])) {
				throw new RedirectException('Ошибка', '/fleet/shortcut/');
			}

			if ($request->has('delete')) {
				unset($scarray[$id]);
				$inf['fleet_shortcut'] = implode("\r\n", $scarray);

				Models\UserDetail::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

				if (Session::has('fleet_shortcut')) {
					Session::remove('fleet_shortcut');
				}

				throw new RedirectException("Ссылка была успешно удалена!", "/fleet/shortcut/");
			} else {
				$r = explode(",", $scarray[$id]);

				$r[0] = strip_tags(str_replace(',', '', $request->post('n', '')));
				$r[1] = (int) $request->post('g', 0);
				$r[2] = (int) $request->post('s', 0);
				$r[3] = (int) $request->post('p', 0);
				$r[4] = (int) $request->post('t', 0);

				if ($r[1] < 1 || $r[1] > config('game.maxGalaxyInWorld')) {
					$r[1] = 1;
				}
				if ($r[2] < 1 || $r[2] > config('game.maxSystemInGalaxy')) {
					$r[2] = 1;
				}
				if ($r[3] < 1 || $r[3] > config('game.maxPlanetInSystem')) {
					$r[3] = 1;
				}
				if ($r[4] != 1 && $r[4] != 2 && $r[4] != 3 && $r[4] != 5) {
					$r[4] = 1;
				}

				$scarray[$id] = implode(",", $r);
				$inf['fleet_shortcut'] = implode("\r\n", $scarray);

				Models\UserDetail::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

				if (Session::has('fleet_shortcut')) {
					Session::remove('fleet_shortcut');
				}

				throw new RedirectException("Ссылка была обновлена!", "/fleet/shortcut/");
			}
		}

		if (!$inf['fleet_shortcut']) {
			throw new RedirectException("Ваш список быстрых ссылок пуст!", "/fleet/shortcut/");
		}

		$scarray = explode("\r\n", $inf['fleet_shortcut']);

		if (!isset($scarray[$id])) {
			throw new RedirectException("Данной ссылки не существует!", "/fleet/shortcut/");
		}

		$c = explode(',', $scarray[$id]);

		return [
			'id' => $id,
			'name' => $c[0],
			'galaxy' => (int) $c[1],
			'system' => (int) $c[2],
			'planet' => (int) $c[3],
			'type' => (int) $c[4],
		];
	}
}
