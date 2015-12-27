<?php

namespace App\Controllers;

use Xcms\db;
use Xnova\User;
use Xnova\pageHelper;

class RefersController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$refers = db::query("SELECT u.id, u.username, u.lvl_minier, u.lvl_raid, ui.register_time FROM game_refs r LEFT JOIN game_users u ON u.id = r.r_id LEFT JOIN game_users_info ui ON ui.id = r.r_id WHERE r.u_id = " . user::get()->getId() . " ORDER BY u.id DESC;");

		$parse['ref'] = array();

		while ($refer = db::fetch_assoc($refers))
		{
			$parse['ref'][] = $refer;
		}

		$refers = db::query("SELECT u.id, u.username FROM game_refs r LEFT JOIN game_users u ON u.id = r.u_id WHERE r.r_id = " . user::get()->getId() . "", true);

		if (isset($refers['id']))
			$parse['you'] = $refers;

		$this->setTemplate('refers');
		$this->set('parse', $parse);

		$this->setTitle('Рефералы');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>