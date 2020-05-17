<?php

namespace Xnova\Providers;

use Illuminate\Auth\EloquentUserProvider as UserProvider;
use Xnova\User;

class AuthUserProvider extends UserProvider
{
	public function retrieveByCredentials(array $credentials): ?User
	{
		if (empty($credentials) || !isset($credentials['email'])) {
			return null;
		}

		return $this->newModelQuery()
			->select(['users.*', 'info.password'])
			->join('accounts as info', 'info.id', '=', 'users.id')
			->where('info.email', $credentials['email'])
			->get()->first();
	}
}
