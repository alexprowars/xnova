<?php

namespace App\Models;

use App\Engine\EntityFactory;
use Illuminate\Database\Eloquent\Model;

class PlanetEntity extends Model
{
	protected $table = 'planets_entities';
	protected $guarded = false;

	protected $attributes = [
		'amount' => 0,
		'factor' => 10,
	];

	public function planet()
	{
		return $this->belongsTo(Planet::class, 'planet_id');
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
