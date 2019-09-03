<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Builder;
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

	const TYPE_BUILD = 'build';
	const TYPE_TECH = 'tech';
	const TYPE_UNIT = 'unit';

	const OPERATION_BUILD = 'build';
	const OPERATION_DESTROY = 'destroy';
}