<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fleet extends Model
{
	protected $table = 'fleets';
	protected $guarded = false;

	protected $casts = [
		'fleet_array' => 'json:unicode',
		'start_date' => 'immutable_datetime',
		'end_date' => 'immutable_datetime',
		'end_stay' => 'immutable_datetime',
		'mission' => Mission::class,
		'start_type' => PlanetType::class,
		'end_type' => PlanetType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<User, $this> */
	public function target(): BelongsTo
	{
		return $this->belongsTo(User::class, 'target_user_id');
	}

	/** @return BelongsTo<Assault, $this> */
	public function assault(): BelongsTo
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

		return '<a href="' . $uri . '" ' . $FleetType . '>[' . $this->splitStartPosition() . ']</a>';
	}

	public function getTargetAdressLink($FleetType = '')
	{
		$uri = route('galaxy', [
			'galaxy' => $this->end_galaxy,
			'system' => $this->end_system,
		], false);

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
		return ($this->mess == 0 || (($this->mess == 3 && $this->mission != Mission::Expedition) && $this->mission != Mission::Rak && $this->target_user_id != 1));
	}

	public function getOriginCoordinates($withType = true): Coordinates
	{
		return new Coordinates($this->start_galaxy, $this->start_system, $this->start_planet, $withType ? $this->start_type : null);
	}

	public function getDestinationCoordinates($withType = true): Coordinates
	{
		return new Coordinates($this->end_galaxy, $this->end_system, $this->end_planet, $withType ? $this->end_type : null);
	}

	/**
	 * @param Builder<$this> $query
	 * @param FleetDirection $position
	 * @param Coordinates $target
	 * @return Builder<$this>
	 */
	public function scopeCoordinates(Builder $query, FleetDirection $position, Coordinates $target): Builder
	{
		return $query->where($position->value . '_galaxy', $target->getGalaxy())
			->where($position->value . '_system', $target->getSystem())
			->where($position->value . '_planet', $target->getPlanet())
			->when(
				$target->getType(),
				fn(Builder $query) => $query->where($position->value . '_type', $target->getType())
			);
	}
}
