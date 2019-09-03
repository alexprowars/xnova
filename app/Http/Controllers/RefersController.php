<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Xnova\Controller;

class RefersController extends Controller
{
	public function index ()
	{
		$refers = DB::select("SELECT u.id, u.username, u.lvl_minier, u.lvl_raid, ui.create_time FROM refs r LEFT JOIN users u ON u.id = r.r_id LEFT JOIN users_info ui ON ui.id = r.r_id WHERE r.u_id = " . $this->user->getId() . " ORDER BY u.id DESC;");

		$parse['ref'] = [];

		foreach ($refers as $refer)
		{
			$parse['ref'][] = (array) $refer;
		}

		$refers = DB::selectOne("SELECT u.id, u.username FROM refs r LEFT JOIN users u ON u.id = r.u_id WHERE r.r_id = " . $this->user->getId() . "");

		if ($refers)
			$parse['you'] = (array) $refers;

		$this->setTitle('Рефералы');
		$this->showTopPanel(false);

		return $parse;
	}
}