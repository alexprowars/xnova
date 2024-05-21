<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $guarded = [];
}
