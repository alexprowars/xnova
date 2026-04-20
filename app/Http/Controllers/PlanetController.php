<?php

namespace App\Http\Controllers;

use App\Engine\Enums\FleetDirection;
use App\Exceptions\Exception;
use App\Models\Fleet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlanetController extends Controller
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

	public function delete(): void
	{
		if ($this->user->planet_id == $this->planet->id) {
			throw new Exception(__('overview.deletemessage_wrong'));
		}

		$checkFleets = Fleet::query()
			->where(fn(Builder $query) => $query->coordinates(FleetDirection::START, $this->planet->coordinates))
			->orWhere(fn(Builder $query) => $query->coordinates(FleetDirection::END, $this->planet->coordinates))
			->exists();

		if ($checkFleets) {
			throw new Exception(__('overview.planet_delete_fleet_in_transit'));
		}

		$destruyed = now()->addDay();

		$this->planet->destroyed_at = $destruyed;
		$this->planet->user_id = null;
		$this->planet->update();

		$this->user->planet_current = $this->user->planet_id;
		$this->user->update();

		if ($this->planet->moon) {
			$this->planet->moon->update([
				'destroyed_at' => $destruyed,
				'user_id' => null,
			]);

			$this->planet->moon->queue()->delete();
		}

		$this->planet->queue()->delete();

		Cache::forget('app::planetlist_' . $this->user->id);
	}

	public function rename(Request $request): void
	{
		$name = strip_tags(trim($request->post('name', '')));

		if (empty($name)) {
			throw new Exception(__('overview.planet_rename_prompt'));
		}

		if (!preg_match("/^[a-zA-Zа-яА-Я0-9_.,\-!?* ]+$/u", $name)) {
			throw new Exception(__('overview.planet_name_invalid_chars'));
		}

		if (mb_strlen($name) <= 1 || mb_strlen($name) >= 20) {
			throw new Exception(__('overview.planet_name_invalid_length'));
		}

		$this->planet->name = $name;
		$this->planet->update();
	}

	public function image(Request $request): void
	{
		if ($this->user->credits < 1) {
			throw new Exception(__('overview.insufficient_credits'));
		}

		$image = (int) $request->post('image', 0);
		$type  = '';

		foreach ($this->planetImages as $t => $max) {
			if (str_contains($this->planet->image, $t)) {
				$type = $t;
			}
		}

		if ($image <= 0 || $image > $this->planetImages[$type]) {
			throw new Exception(__('overview.insufficient_cheat_skills'));
		}

		$this->planet->image = $type . 'planet' . ($image < 10 ? '0' : '') . $image;
		$this->planet->update();

		$this->user->credits--;
		$this->user->update();
	}
}
