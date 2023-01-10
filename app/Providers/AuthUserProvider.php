<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider as UserProvider;
use App\User;

class AuthUserProvider extends UserProvider
{
	public function retrieveByCredentials(array $credentials): ?User
	{
		if (empty($credentials) || !isset($credentials['email'])) {
			return null;
		}

		return $this->newModelQuery()
			->select(['users.*', 'info.password'])
			->join('user_details as info', 'info.id', '=', 'users.id')
			->where('info.email', $credentials['email'])
			->get()->first();
	}
}
