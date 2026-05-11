<?php

namespace App\Models;

use App\Engine\EntityFactory;
use App\Models\Collections\PlanetEntityCollection;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[CollectedBy(PlanetEntityCollection::class)]
class PlanetEntity extends Model
{
	protected $table = 'planets_entities';
	protected $guarded = [];
	public $timestamps = false;

	protected $attributes = [
		'amount' => 0,
	];

	/**
	 * @return array{
	 *     props: 'Illuminate\Database\Eloquent\Casts\AsArrayObject',
	 * }
	 */
	protected function casts(): array
	{
		return [
			'props' => AsArrayObject::class,
		];
	}

	/** @return BelongsTo<Planet, $this> */
	public function planet(): BelongsTo
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}

	public function getFactor(): int
	{
		return $this->props['factor'] ?? 10;
	}

	public function setFactor(int $level): self
	{
		$this->props ??= [];
		$this->props['factor'] = $level;

		return $this;
	}

	public function setLevel(int $level): self
	{
		$this->setAttribute('amount', $level);

		return $this;
	}

	public function unit()
	{
		return EntityFactory::get(
			$this->entity_id,
			$this->amount,
			$this->relationLoaded('planet') ? $this->planet : null
		);
	}
}
