<?php

namespace App\Http\Controllers;

use App\Models\User;

class ContactsController extends Controller
{
	public function index()
	{
		$users = User::query()
			->with(['roles'])
			->whereHas('roles')
			->orderByDesc('id')
			->get();

		$items = [];

		foreach ($users as $user) {
			$items[] = [
				'id' 	=> $user->id,
				'name' 	=> $user->username,
				'role' 	=> $user->roles->first()->name,
				'email' => $user->email,
				'about' => preg_replace("/(\r\n)/u", "<br>", stripslashes($user->about)),
			];
		}

		return $items;
	}
}
