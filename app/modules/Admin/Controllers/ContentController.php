<?php

namespace Admin\Controllers;

use Admin\Controller;

/**
 * @RoutePrefix("/admin/content")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ContentController extends Controller
{
	const CODE = 'content';

	public function initialize ()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'content',
			'title' => 'Контент',
			'icon'	=> 'doc',
			'sort'	=> 180
		]];
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
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$info = $this->db->query("SELECT * FROM game_content WHERE id = '".intval($id)."'")->fetch();

		$this->view->setVar('info', $info);
	}
}