<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 * @property string $html
 */
class Content extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $guarded = [];
}
