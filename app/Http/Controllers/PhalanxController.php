<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Fleet;
use Xnova\Models;
use Xnova\Controller;

class PhalanxController extends Controller
{
	protected $loadPlanet = true;

	public function index ()
	{
		if ($this->user->vacation > 0)
			throw new PageException('Нет доступа!');

		$g = (int) Request::post('galaxy');
		$s = (int) Request::post('system');
		$i = (int) Request::post('planet');

		$consomation = 5000;

		if ($g < 1 || $g > Config::get('settings.maxGalaxyInWorld'))
			$g = $this->planet->galaxy;
		if ($s < 1 || $s > Config::get('settings.maxSystemInGalaxy'))
			$s = $this->planet->system;
		if ($i < 1 || $i > Config::get('settings.maxPlanetInSystem'))
			$i = $this->planet->planet;

		$phalanx = $this->planet->getBuildLevel('phalanx');

		$systemdol 	= $this->planet->system - pow($phalanx, 2);
		$systemgora = $this->planet->system + pow($phalanx, 2);

		if ($this->planet->planet_type != 3)
			throw new PageException("Вы можете использовать фалангу только на луне!");
		elseif ($phalanx == 0)
			throw new PageException("Постройте сначало сенсорную фалангу");
		elseif ($this->planet->deuterium < $consomation)
			throw new PageException("<b>Недостаточно дейтерия для использования. Необходимо: ".$consomation.".</b>");
		elseif (($s <= $systemdol OR $s >= $systemgora) OR $g != $this->planet->galaxy)
			throw new PageException("Вы не можете сканировать данную планету. Недостаточный уровень сенсорной фаланги.");

		$this->planet->deuterium -= $consomation;
		$this->planet->update();

		$planet = Models\Planets::query()
			->where('galaxy', $g)
			->where('system', $s)
			->where('planet', $i)
			->count();

		if (!$planet)
			throw new RedirectException('Чит детектед! Режим бога активирован! Приятной игры!', '');

		$fleets = Models\Fleet::query()
			->where(function (Builder $query) use ($g, $s, $i)
			{
				$query->where('start_galaxy', $g)
					->where('start_system', $s)
					->where('start_planet', $i)
					->where('start_type', '!=', 3);
			})
			->orWhere(function (Builder $query) use ($g, $s, $i)
			{
				$query->where('end_galaxy', $g)
					->where('end_system', $s)
					->where('end_planet', $i);
			})
			->orderBy('start_time', 'asc')
			->get();

		$list = [];

		foreach ($fleets as $ii => $row)
		{
			$end = !($row->start_galaxy == $g && $row->start_system == $s && $row->start_planet == $i);

			$color = ($row->mission != 6) ? 'lime' : 'orange';

			if ($row->start_type == 3)
				$type = "лун";
			else
				$type = "планет";

			if ($row->end_type == 3)
				$type2 = "лун";
			else
				$type2 = "планет";

			if ($row->start_time > time() && $end && !($row->start_type == 3 && ($row->end_type == 2 || $row->end_type == 3)))
			{
				$list[] = [
					'time' => (int) $row->start_time,
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type.'ы',
					'type_2' => $type2.'у',
					'planet_name' => $row->owner_name,
					'planet_position' => $row->splitStartPosition(),
					'target_name' => $row->target_owner_name,
					'target_position' => $row->splitTargetPosition(),
					'mission' => __('main.type_mission.'.$row->mission),
					'color' => $color,
					'direction' => 1
				];
			}

			if ($row->mission <> 4 && !$end && $row->start_type != 3)
			{
				$list[] = [
					'time' => (int) $row->end_time,
					'fleet' => Fleet::CreateFleetPopupedFleetLink($row, 'флот', '', $this->user),
					'type_1' => $type2.'ы',
					'type_2' => $type.'у',
					'planet_name' => $row->target_owner_name,
					'planet_position' => $row->splitTargetPosition(),
					'target_name' => $row->owner_name,
					'target_position' => $row->splitStartPosition(),
					'mission' => __('main.type_mission.'.$row->mission),
					'color' => $color,
					'direction' => 2
				];
			}
		}

		$this->setTitle('Сенсорная фаланга');

		$this->showTopPanel(false);
		$this->showLeftPanel(false);

		return [
			'items' => $list
		];
	}
}