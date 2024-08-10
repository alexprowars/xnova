<?php

namespace App\Http\Controllers;

use App\Models\User;

class ContactsController extends Controller
{
	public function index()
	{
		$users = User::query()
			->where('authlevel', '>', 0)
			->orderByDesc('authlevel')
			->get();

		$items = [];

		foreach ($users as $user) {
			$items[] = [
				'id' 	=> $user->id,
				'name' 	=> $user->username,
				'auth' 	=> __('main.user_level', $user->authlevel),
				'mail' 	=> $user->email,
				'info' 	=> preg_replace("/(\r\n)/u", "<br>", stripslashes($user->about)),
			];
		}

		return $items;
	}
}
