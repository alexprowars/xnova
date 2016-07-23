<?php

namespace Admin\Controllers;

use Admin\Controller;

class ErrorsController extends Controller
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

		$query = $this->db->query("SELECT * FROM game_errors");

		$result['total'] = $query->numRows();

		while ($e = $query->fetch())
		{
			$result['rows'][] = Array
			(
				'ID' 		=> $e['error_id'],
				'TYPE'		=> $e['error_type'],
				'SENDER'	=> $e['error_sender'],
				'TIME'		=> $e['error_time'],
				'TEXT'		=> htmlspecialchars($e['error_text'])
			);
		}

		$this->view->setVar('parse', $result);
		$this->tag->setTitle("Ошибки SQL");
	}

	public function deleteAction ($id)
	{
		$this->db->query("DELETE FROM game_errors WHERE `error_id` = '" . intval($id) . "'");
		$this->indexAction();
	}

	public function clearAction ()
	{
		$this->db->query("TRUNCATE TABLE game_errors");
		$this->indexAction();
	}
}