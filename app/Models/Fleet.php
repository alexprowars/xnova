<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Fleet
 */
class Fleet extends Model
{
	protected $guarded = [];

	protected function casts(): array
	{
		return [
			'fleet_array' => 'array',
			'start_time' => 'immutable_datetime',
			'end_time' => 'immutable_datetime',
			'end_stay' => 'immutable_datetime',
			'mission' => Mission::class,
			'start_type' => PlanetType::class,
			'end_type' => PlanetType::class,
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function target()
	{
		return $this->belongsTo(User::class, 'target_user_id');
	}

	public function assault()
	{
		return $this->belongsTo(Assault::class, 'assault_id');
	}

	public function splitStartPosition()
	{
		return $this->start_galaxy . ':' . $this->start_system . ':' . $this->start_planet;
	}

	public function splitTargetPosition()
	{
		return $this->end_galaxy . ':' . $this->end_system . ':' . $this->end_planet;
	}

	public function getStartAdressLink($FleetType = '')
	{
		$uri = route('galaxy', [
			'galaxy' => $this->start_galaxy,
			'system' => $this->start_system,
		], false);

		$uri = str_replace('/api', '', $uri);

		return '<a href="' . $uri . '" ' . $FleetType . '>[' . $this->splitStartPosition() . ']</a>';
	}

	public function getTargetAdressLink($FleetType = '')
	{
		$uri = route('galaxy', [
			'galaxy' => $this->end_galaxy,
			'system' => $this->end_system,
		], false);

		$uri = str_replace('/api', '', $uri);

		return '<a href="' . $uri . '" ' . $FleetType . '>[' . $this->splitTargetPosition() . ']</a>';
	}

	public function getTotalShips()
	{
		$result = 0;

		$data = $this->getShips();

		foreach ($data as $type) {
			$result += $type['count'];
		}

		return $result;
	}

	public function getShips()
	{
		if (empty($this->fleet_array)) {
			return [];
		}

		$result = [];

		foreach ($this->fleet_array as $fleet) {
			if (empty($fleet['id'])) {
				continue;
			}

			$fleetId = (int) $fleet['id'];

			$result[$fleetId] = [
				'id' => $fleetId,
				'count' => isset($fleet['count']) ? (int) $fleet['count'] : 0,
			];

			if (isset($fleet['target'])) {
				$result[$fleetId]['target'] = (int) $fleet['target'];
			}
		}

		return $result;
	}

	public function canBack()
	{
		return ($this->mess == 0 || (($this->mess == 3 && $this->mission != 15) && $this->mission != Mission::Rak && $this->target_user_id != 1));
	}

	public function getOriginCoordinates($withType = true): Coordinates
	{
		return new Coordinates($this->start_galaxy, $this->start_system, $this->start_planet, $withType ? $this->start_type : null);
	}

	public function getDestinationCoordinates($withType = true): Coordinates
	{
		return new Coordinates($this->end_galaxy, $this->end_system, $this->end_planet, $withType ? $this->end_type : null);
	}

	public function return(array $attributes = [])
	{
		$this->mess = 1;
		$this->updated_at = $this->end_time;

		$this->update($attributes);
		$this->assault?->delete();
	}

	public function scopeCoordinates(Builder $query, FleetDirection $position, Coordinates $target)
	{
		$query->where($position->value . '_galaxy', $target->getGalaxy())
			->where($position->value . '_system', $target->getSystem())
			->where($position->value . '_planet', $target->getPlanet());

		if ($target->getType()) {
			$query->where($position->value . '_type', $target->getType());
		}
	}
}
