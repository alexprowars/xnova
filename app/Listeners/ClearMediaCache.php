<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ClearMediaCache
{
	public function handle(MediaHasBeenAddedEvent $event)
	{
		$media = $event->media;

		if ($media->model instanceof User) {
			Cache::forget('media::user_' . $media->model->id);
		}
	}
}
