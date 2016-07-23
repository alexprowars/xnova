<?php

namespace Admin\Controllers;

use Admin\Controller;

class LoadController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		$result = [];
		$result['rows'] = [];

		$query = $this->db->query("SELECT * FROM game_log_load WHERE time >= ".(time() - 86400)." ORDER BY time ASC");

		while ($e = $query->fetch())
		{
			$result['rows'][] = Array
			(
				'TIME'	=> $e['time'],
				'LOAD'	=> json_decode($e['value'], true)
			);
		}
		
		$this->view->setVar('parse', $result);
		$this->tag->setTitle("Загрузка сервера");
	}
}