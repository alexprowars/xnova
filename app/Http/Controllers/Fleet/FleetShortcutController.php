<?php

namespace Xnova\Http\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Xnova\Controller;
use Xnova\Models;
use Xnova\Exceptions\RedirectException;

class FleetShortcutController extends Controller
{
	public function index ()
	{
		$this->setTitle('Закладки');

		$inf = Models\UsersInfo::query()->find($this->user->id, ['fleet_shortcut']);

		if (Request::has('add'))
		{
			if (Request::instance()->isMethod('post'))
			{
				$name = Request::post('n', '');

				if ($name == '' || !preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name))
					$name = 'Планета';

				$g = (int) Request::post('g', 0);
				$s = (int) Request::post('s', 0);
				$p = (int) Request::post('p', 0);
				$t = (int) Request::post('t', 0);

				if ($g < 1 || $g > Config::get('game.maxGalaxyInWorld'))
					$g = 1;
				if ($s < 1 || $s > Config::get('game.maxSystemInGalaxy'))
					$s = 1;
				if ($p < 1 || $p > Config::get('game.maxPlanetInSystem'))
					$p = 1;
				if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
					$t = 1;

				$inf['fleet_shortcut'] .= strip_tags(str_replace(',', '', $name)) . "," . $g . "," . $s . "," . $p . "," . $t . "\r\n";

				Models\UsersInfo::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

				if (Session::has('fleet_shortcut'))
					Session::remove('fleet_shortcut');

				throw new RedirectException("Ссылка на планету добавлена!", "/fleet/shortcut/");
			}

			$g = (int) Request::input('g', 0);
			$s = (int) Request::input('s', 0);
			$p = (int) Request::input('p', 0);
			$t = (int) Request::input('t', 0);

			if ($g < 1 || $g > Config::get('game.maxGalaxyInWorld'))
				$g = 1;
			if ($s < 1 || $s > Config::get('game.maxSystemInGalaxy'))
				$s = 1;
			if ($p < 1 || $p > Config::get('game.maxPlanetInSystem'))
				$p = 1;
			if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
				$t = 1;

			return [
				'id' => -1,
				'name' => '',
				'galaxy' => $g,
				'system' => $s,
				'planet' => $p,
				'type' => $t,
			];
		}
		elseif (Request::has('view'))
		{
			$id = (int) Request::query('view', 0);

			if (Request::instance()->isMethod('post'))
			{
				$scarray = explode("\r\n", $inf['fleet_shortcut']);

				if (!isset($scarray[$id]))
					throw new RedirectException('Ошибка', '/fleet/shortcut/');

				if (Request::has('delete'))
				{
					unset($scarray[$id]);
					$inf['fleet_shortcut'] = implode("\r\n", $scarray);

					Models\UsersInfo::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

					if (Session::has('fleet_shortcut'))
						Session::remove('fleet_shortcut');

					throw new RedirectException("Ссылка была успешно удалена!", "/fleet/shortcut/");
				}
				else
				{
					$r = explode(",", $scarray[$id]);

					$r[0] = strip_tags(str_replace(',', '', Request::post('n', '')));
					$r[1] = (int) Request::post('g', 0);
					$r[2] = (int) Request::post('s', 0);
					$r[3] = (int) Request::post('p', 0);
					$r[4] = (int) Request::post('t', 0);

					if ($r[1] < 1 || $r[1] > Config::get('game.maxGalaxyInWorld'))
						$r[1] = 1;
					if ($r[2] < 1 || $r[2] > Config::get('game.maxSystemInGalaxy'))
						$r[2] = 1;
					if ($r[3] < 1 || $r[3] > Config::get('game.maxPlanetInSystem'))
						$r[3] = 1;
					if ($r[4] != 1 && $r[4] != 2 && $r[4] != 3 && $r[4] != 5)
						$r[4] = 1;

					$scarray[$id] = implode(",", $r);
					$inf['fleet_shortcut'] = implode("\r\n", $scarray);

					Models\UsersInfo::query()->where('id', $this->user->getId())->update(['fleet_shortcut' => $inf['fleet_shortcut']]);

					if (Session::has('fleet_shortcut'))
						Session::remove('fleet_shortcut');

					throw new RedirectException("Ссылка была обновлена!", "/fleet/shortcut/");
				}
			}

			if ($inf['fleet_shortcut'])
			{
				$scarray = explode("\r\n", $inf['fleet_shortcut']);

				if (isset($scarray[$id]))
				{
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
				else
					throw new RedirectException("Данной ссылки не существует!", "/fleet/shortcut/");
			}
			else
				throw new RedirectException("Ваш список быстрых ссылок пуст!", "/fleet/shortcut/");
		}
		else
		{
			$links = [];

			if ($inf['fleet_shortcut'])
			{
				$scarray = explode("\r\n", $inf['fleet_shortcut']);

				foreach ($scarray as $a => $b)
				{
					if ($b != '')
					{
						$c = explode(',', $b);

						$type = '';

						if ($c[4] == 2)
							$type = " (E)";
						elseif ($c[4] == 3)
							$type = " (L)";
						elseif ($c[4] == 5)
							$type = " (B)";

						$links[] =
						[
							'name' => $c[0],
							'galaxy' => $c[1],
							'system' => $c[2],
							'planet' => $c[3],
							'type'=> $type
						];
					}
				}
			}

			return [
				'items' => $links
			];
		}
	}
}