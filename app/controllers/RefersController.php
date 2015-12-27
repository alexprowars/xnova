<?php

namespace App\Controllers;

class RefersController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function show ()
	{
		$refers = $this->db->query("SELECT u.id, u.username, u.lvl_minier, u.lvl_raid, ui.register_time FROM game_refs r LEFT JOIN game_users u ON u.id = r.r_id LEFT JOIN game_users_info ui ON ui.id = r.r_id WHERE r.u_id = " . $this->user->getId() . " ORDER BY u.id DESC;");

		$parse['ref'] = array();

		while ($refer = $refers->fetch())
		{
			$parse['ref'][] = $refer;
		}

		$refers = $this->db->query("SELECT u.id, u.username FROM game_refs r LEFT JOIN game_users u ON u.id = r.u_id WHERE r.r_id = " . $this->user->getId() . "")->fetch();

		if (isset($refers['id']))
			$parse['you'] = $refers;

		$this->view->pick('refers');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Рефералы');
		$this->showTopPanel(false);
	}
}

?>