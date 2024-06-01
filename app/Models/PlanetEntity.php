<?php

namespace App\Models;

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
}
