<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanetEntity extends Model
{
	protected $table = 'planet_entities';
	protected $guarded = [];
	public $timestamps = false;

	public static function createEmpty(int $entityId, int $level = 0): self
	{
		return new static([
			'entity_id' => $entityId,
			'amount' => $level,
		]);
	}
}
