<?php

use Illuminate\Database\Eloquent\Model;

expect()->extend('toBeModel', function (Model $model) {
	return $this->is($model)->toBeTrue();
});
