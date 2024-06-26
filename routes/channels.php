<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['api', 'auth']]);

Broadcast::channel('user.{id}', function ($user, $id) {
	return (int) $user->id === (int) $id;
});
