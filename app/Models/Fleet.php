<?php

namespace App\Models;

use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntity;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\Mission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property FleetEntityCollection $entities
 */
class Fleet extends Model
{
	protected $table = 'fleets';
	protected $guarded = [];
	protected $casts = [
		'start_date' => 'immutable_datetime',
		'end_date' => 'immutable_datetime',
		'end_stay' => 'immutable_datetime',
		'mission' => Mission::class,
		'start_type' => PlanetType::class,
		'end_type' => PlanetType::class,
	];

	/**
	 * @return array{
	 *     entities: 'Illuminate\Database\Eloquent\Casts\AsCollection',
	 * }
	 */
	protected function casts(): array
	{
		return [
			'entities' => AsCollection::using(FleetEntityCollection::class, FleetEntity::class),
		];
	}

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

	public function getStartAdressLink($type = '')
	{
		$uri = route('galaxy', [
			'galaxy' => $this->start_galaxy,
			'system' => $this->start_system,
		], false);

		return '<a href="' . $uri . '" ' . $type . '>[' . $this->splitStartPosition() . ']</a>';
	}

	public function getTargetAdressLink($type = '')
	{
		$uri = route('galaxy', [
			'galaxy' => $this->end_galaxy,
			'system' => $this->end_system,
		], false);

		return '<a href="' . $uri . '" ' . $type . '>[' . $this->splitTargetPosition() . ']</a>';
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
