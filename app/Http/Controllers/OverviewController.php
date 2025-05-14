<?php

namespace App\Http\Controllers;

use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Fleet\Mission;
use App\Engine\Game;
use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Exceptions\Exception;
use App\Http\Resources\FleetRow;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OverviewController extends Controller
{
	protected array $planetImages = [
		'trocken' => 20,
		'wuesten' => 4,
		'dschjungel' => 19,
		'normaltemp' => 15,
		'gas' => 16,
		'wasser' => 18,
		'eis' => 20,
	];

	public function index()
	{
		$fleets = Fleet::query()
			->with('user')
			->whereBelongsTo($this->user)
			->orWhereBelongsTo($this->user, 'target')
			->get();

		$flotten = [];
		$aks = [];

		foreach ($fleets as $fleet) {
			if ($fleet->user_id == $this->user->id) {
				if ($fleet->start_time->isFuture()) {
					$flotten[] = FleetRow::make($fleet, 0, true);
				}

				if ($fleet->end_stay?->isFuture()) {
					$flotten[] = FleetRow::make($fleet, 1, true);
				}

				if (!($fleet->mission == Mission::Colonization && $fleet->mess == 0)) {
					if (($fleet->end_time->isFuture() && $fleet->mission != Mission::Stay) or ($fleet->mess == 1 && $fleet->mission == Mission::Stay)) {
						$flotten[] = FleetRow::make($fleet, 2, true);
					}
				}

				if ($fleet->assault_id && !in_array($fleet->assault_id, $aks)) {
					$AKSFleets = Fleet::query()
						->where('assault_id', $fleet->assault_id)
						->where('user_id', '!=', $this->user->id)
						->where('mess', 0)
						->get();

					foreach ($AKSFleets as $AKFleet) {
						$flotten[] = FleetRow::make($AKFleet, 0, false);
					}

					$aks[] = $fleet->assault_id;
				}
			} elseif ($fleet->mission != Mission::Recycling) {
				if ($fleet->start_time->isFuture()) {
					$flotten[] = FleetRow::make($fleet, 0, false);
				}

				if ($fleet->mission == Mission::StayAlly && $fleet->end_stay?->isFuture()) {
					$flotten[] = FleetRow::make($fleet, 1, false);
				}
			}
		}

		usort($flotten, fn ($a, $b) => $a['time'] <=> $b['time']);

		$parse = [];
		$parse['moon'] = false;

		if ($this->planet->moon_id && $this->planet->planet_type != PlanetType::MOON && $this->planet->id) {
			/** @var ?Planet $lune */
			$lune = Cache::remember('app::lune_' . $this->planet->moon_id, 300, function () {
				return Planet::query()
					->select(['id', 'name', 'image'])
					->where('id', $this->planet->moon_id)
					->where('planet_type', PlanetType::MOON)
					->whereNull('destruyed_at')
					->first();
			});

			if ($lune) {
				$parse['moon'] = [
					'id' => $lune->id,
					'name' => $lune->name,
					'image' => $lune->image,
				];
			}
		}

		$parse['fleets'] = $flotten;
		$parse['queue'] = $this->getQueue();

		$parse['lvl'] = [
			'mine' => [
				'p' => $this->user->xpminier,
				'l' => $this->user->lvl_minier,
				'u' => $this->user->lvl_minier ** 3,
			],
			'raid' => [
				'p' => $this->user->xpraid,
				'l' => $this->user->lvl_raid,
				'u' => $this->user->lvl_raid ** 2,
			],
		];

		$parse['links'] = $this->user->links;
		$parse['refers'] = $this->user->refers;
		$parse['noob'] = $this->user->isNoobProtection();

		$parse['bonus'] = $this->user->daily_bonus->isPast();

		if ($parse['bonus']) {
			$bonus = $this->user->daily_bonus_factor + 1;

			if ($bonus > 50) {
				$bonus = 50;
			}

			if ($this->user->daily_bonus->subDay()->isPast()) {
				$bonus = 1;
			}

			$parse['bonus_count'] = $bonus * 500 * Game::getSpeed('mine');
		}

		$parse['resource_notify'] = false;

		foreach (Vars::getResources() as $res) {
			$entity = $this->planet->entities->where('entity_id', Vars::getIdByName($res . '_mine'))->first();

			if ($this->planet->getLevel($res . '_mine') && !$entity?->factor) {
				$parse['resource_notify'] = true;
			}
		}

		return $parse;
	}

	public function delete($planetId)
	{
		$planet = Planet::find($planetId);

		if (!$planet) {
			throw new Exception('Планета не найдена');
		}

		if ($this->user->id != $planet->user_id) {
			throw new Exception('Удалить планету может только владелец');
		}

		if ($this->user->planet_id == $planet->id) {
			throw new Exception(__('overview.deletemessage_wrong'));
		}

		$checkFleets = Fleet::query()
			->where(fn (Builder $query) => $query->coordinates(FleetDirection::START, $planet->getCoordinates()))
			->orWhere(fn (Builder $query) => $query->coordinates(FleetDirection::END, $planet->getCoordinates()))
			->exists();

		if ($checkFleets) {
			throw new Exception('Нельзя удалять планету если с/на неё летит флот');
		}

		$destruyed = now()->addDay();

		$planet->destruyed_at = $destruyed;
		$planet->user_id = null;
		$planet->update();

		$this->user->planet_current = $this->user->planet_id;
		$this->user->update();

		if ($planet->moon_id) {
			Planet::where('id', $planet->moon_id)
				->update([
					'destruyed_at' => $destruyed,
					'user_id' => null,
				]);

			Queue::where('planet_id', $planet->moon_id)
				->delete();
		}

		$planet->queue()->delete();

		Cache::forget('app::planetlist_' . $this->user->id);
	}

	public function rename()
	{
		$parse = [];
		$parse['images'] = $this->planetImages;
		$parse['type'] = '';

		foreach ($parse['images'] as $type => $max) {
			if (str_contains($this->planet->image, $type)) {
				$parse['type'] = $type;
			}
		}

		return $parse;
	}

	public function renameAction(Request $request)
	{
		$name = strip_tags(trim($request->post('name', '')));

		if ($name == '') {
			throw new Exception('Ввведите новое название планеты');
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new Exception('Введённое название содержит недопустимые символы');
		}

		if (mb_strlen($name) <= 1 || mb_strlen($name) >= 20) {
			throw new Exception('Введённо слишком длинное или короткое название планеты');
		}

		$this->planet->name = $name;
		$this->planet->update();
	}

	public function image(Request $request)
	{
		if ($this->user->credits < 1) {
			throw new Exception('Недостаточно кредитов');
		}

		$image = (int) $request->post('image', 0);
		$type  = '';

		foreach ($this->planetImages as $t => $max) {
			if (str_contains($this->planet->image, $t)) {
				$type = $t;
			}
		}

		if ($image <= 0 || $image > $this->planetImages[$type]) {
			throw new Exception('Недостаточно читерских навыков');
		}

		$this->planet->image = $type . 'planet' . ($image < 10 ? '0' : '') . $image;
		$this->planet->update();

		$this->user->credits--;
		$this->user->update();
	}

	public function daily()
	{
		if ($this->user->daily_bonus?->isFuture()) {
			throw new Exception('Вы не можете получить ежедневный бонус в данное время');
		}

		$factor = $this->user->daily_bonus_factor < 50
			? $this->user->daily_bonus_factor + 1 : 50;

		if (!$this->user->daily_bonus || $this->user->daily_bonus->subDay()->isPast()) {
			$factor = 1;
		}

		$add = $factor * 500 * Game::getSpeed('mine');

		$this->planet->metal += $add;
		$this->planet->crystal += $add;
		$this->planet->deuterium += $add;
		$this->planet->update();

		$this->user->daily_bonus = now()->addSeconds(86400);
		$this->user->daily_bonus_factor = $factor;

		if ($this->user->daily_bonus_factor > 1) {
			$this->user->credits++;
		}

		$this->user->update();

		return [
			'resources' => $add,
			'credits' => $this->user->daily_bonus_factor > 1,
		];
	}

	protected function getQueue()
	{
		$queueList = [];

		$planetsData = Planet::query()
			->whereBelongsTo($this->user)
			->get()->keyBy('id');

		$queueManager = new QueueManager($this->user);

		if ($queueManager->getCount(QueueType::BUILDING)) {
			$queueArray = $queueManager->get(QueueType::BUILDING);

			foreach ($queueArray as $item) {
				/** @var Planet $planet */
				$planet = $planetsData[$item->planet_id];

				$queueList[] = [
					'time' => $item->time_end->utc()->toAtomString(),
					'planet_id' => $item->planet_id,
					'planet_name' => $planet->name,
					'object_id' => $item->object_id,
					'level' => $item->operation == QueueConstructionType::BUILDING ? $item->level - 1 : $item->level + 1,
					'level_to' => $item->level,
				];
			}
		}

		if ($queueManager->getCount(QueueType::RESEARCH)) {
			$queueArray = $queueManager->get(QueueType::RESEARCH);

			foreach ($queueArray as $item) {
				$queueList[] = [
					'time' => $item->time_end->utc()->toAtomString(),
					'planet_id' => $item->planet_id,
					'planet_name' => $planetsData[$item->planet_id]->name,
					'object_id' => $item->object_id,
					'level' => $this->user->getTechLevel($item->object_id),
					'level_to' => $this->user->getTechLevel($item->object_id) + 1,
				];
			}
		}

		if ($queueManager->getCount(QueueType::SHIPYARD)) {
			$queueArray = $queueManager->get(QueueType::SHIPYARD);

			$end = [];

			foreach ($queueArray as $item) {
				$end[$item->planet_id] ??= $item->time;
				$end[$item->planet_id]->addSeconds(((int) $item->time->diffInSeconds($item->time_end)) * $item->level);

				if ($end[$item->planet_id]->isPast()) {
					continue;
				}

				$queueList[] = [
					'time' => $end[$item->planet_id]->utc()->toAtomString(),
					'planet_id' => $item->planet_id,
					'planet_name' => $planetsData[$item->planet_id]->name,
					'object_id' => $item->object_id,
					'level' => $item->level,
				];
			}
		}

		usort($queueList, fn($a, $b) => $a['time'] > $b['time'] ? 1 : -1);

		return $queueList;
	}
}
