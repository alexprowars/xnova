<?php

namespace Xnova\Models\Observers;

use Xnova\User;

class UserObserver
{
	public function saved (User $model)
	{
		$model->_afterUpdateTechs();
	}
}