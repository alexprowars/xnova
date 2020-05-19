<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['api', 'auth']]);

Broadcast::channel('chat.{id}', function ($user, $id) {
	return (int) $user->id === (int) $id;
});
