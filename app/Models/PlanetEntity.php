<?php

namespace App\Models;

use App\Planet;
use Illuminate\Database\Eloquent\Model;

class PlanetEntity extends Model
{
	protected $table = 'planets_entities';
	protected $guarded = [];

	protected $attributes = [
		'amount' => 0,
		'factor' => 10,
	];

	public static function createEmpty(int $entityId, int $level = 0): self
	{
		return new static([
			'entity_id' => $entityId,
			'amount' => $level,
		]);
	}

	public function planet()
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}
}
