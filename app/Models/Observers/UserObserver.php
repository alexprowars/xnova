<?php

namespace App\Models\Observers;

use App\Models\LogStat;
use App\Models\User;

class UserObserver
{
	public function saved(User $model)
	{
		$model->_afterUpdateTechs();
	}

	public function deleting(User $model)
	{
		if ($model->alliance) {
			if ($model->alliance->user_id != $model->id) {
				$model->alliance->deleteMember($model->id);
			} else {
				$model->alliance->delete();
			}
		}

		LogStat::query()->where('object_id', $model->id)->where('type', 1)->delete();
	}
}
