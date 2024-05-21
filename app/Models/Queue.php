<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
	public $timestamps = false;

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
