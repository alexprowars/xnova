<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Controller;

class ContactsController extends Controller
{
	public function index()
	{
		$contacts = [];

		$GameOps = DB::select("SELECT u.id, u.username, u.email, u.authlevel, ui.about FROM users u, users_details ui WHERE ui.id = u.id AND u.authlevel != '0' ORDER BY u.authlevel DESC");

		foreach ($GameOps as $Ops) {
			$contacts[] = [
				'id' 	=> (int) $Ops->id,
				'name' 	=> $Ops->username,
				'auth' 	=> __('main.user_level', $Ops->authlevel),
				'mail' 	=> $Ops->email,
				'info' 	=> preg_replace("/(\r\n)/u", "<br>", stripslashes($Ops->about)),
			];
		}

		return [
			'items' => $contacts
		];
	}
}
