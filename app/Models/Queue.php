<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $object_id
 * @property $planet_id
 * @property $level
 * @property $type
 * @property $operation
 * @property $time
 * @property $time_end
 */
class Queue extends Model
{
	public $timestamps = false;
	public $table = 'queue';

	protected $attributes = [
		'operation' => self::OPERATION_BUILD,
	];

	protected $guarded = [];

	public const TYPE_BUILD = 'build';
	public const TYPE_TECH = 'tech';
	public const TYPE_UNIT = 'unit';

	public const OPERATION_BUILD = 'build';
	public const OPERATION_DESTROY = 'destroy';
}
