<?php
namespace Xnova\Admin\Controllers;

class ContentController extends Application
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

		$query = $this->db->query("SELECT * FROM game_content");

		$result['total'] = $query->numRows();

		while ($e = $query->fetch())
		{
			$result['rows'][] = $e;
		}

		$this->view->setVar('parse', $result);
		$this->tag->setTitle("Контент");
	}

	public function editAction ($id)
	{
		$info = $this->db->query("SELECT * FROM game_content WHERE id = '".intval($id)."'")->fetch();

		$this->view->setVar('info', $info);
	}
}

?>