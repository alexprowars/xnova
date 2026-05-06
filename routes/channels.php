<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['web', 'auth']]);

Broadcast::channel('user.{id}', function (User $user, int | string $id) {
	return $user->id === (int) $id;
});
