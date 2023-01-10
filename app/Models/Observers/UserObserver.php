<?php

namespace App\Models\Observers;

use App\User;

class UserObserver
{
	public function saved(User $model)
	{
		$model->_afterUpdateTechs();
	}

	public function deleted(User $model)
	{
		User::deleteById($model->id);
	}
}
