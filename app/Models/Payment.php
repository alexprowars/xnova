<?php

namespace Xnova\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	use CrudTrait;

	public $timestamps = false;
	protected $guarded = [];

	public function user()
	{
		return $this->hasOne(Users::class, 'id', 'user_id');
	}
}
