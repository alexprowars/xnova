<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $planet_id
 * @property int $entity_id
 * @property int $amount
 * @property int $factor
 */
class PlanetEntity extends Model
{
	protected $table = 'planet_entities';
	protected $guarded = [];
	public $timestamps = false;

	public static function createEmpty(int $entityId, int $level): self
	{
		return new static([
			'entity_id' => $entityId,
			'amount' => $level,
		]);
	}
}
