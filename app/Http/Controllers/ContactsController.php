<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Xnova\Controller;

class ContactsController extends Controller
{
	function index ()
	{
		$contacts = [];

		$GameOps = DB::select("SELECT u.id, u.username, ui.email, u.authlevel, ui.about FROM users u, users_info ui WHERE ui.id = u.id AND u.authlevel != '0' ORDER BY u.authlevel DESC");

		foreach ($GameOps as $Ops)
		{
			$contacts[] = [
				'id' 	=> (int) $Ops->id,
				'name' 	=> $Ops->username,
				'auth' 	=> __('main.user_level', $Ops->authlevel),
				'mail' 	=> $Ops->email,
				'info' 	=> preg_replace("/(\r\n)/u", "<br>", stripslashes($Ops->about)),
			];
		}

		$this->setTitle(__('contact.ctc_title'));
		$this->showTopPanel(false);

		return [
			'items' => $contacts
		];
	}
}