<?php

namespace App\Models;

use App\Engine\EntityCollection;
use App\Engine\EntityFactory;
use Illuminate\Database\Eloquent\Model;

class PlanetEntity extends Model
{
	protected $table = 'planets_entities';
	protected $guarded = [];

	protected $attributes = [
		'amount' => 0,
		'factor' => 10,
	];

	public function planet()
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}

	public function newCollection(array $models = []): EntityCollection
	{
		return new EntityCollection($models);
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
