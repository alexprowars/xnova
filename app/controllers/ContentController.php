<?php

namespace App\Controllers;

class ContentController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}

	function show()
	{
		if (!$this->request->getQuery('article'))
			$this->message('Страница не найдена!');

		$content = $this->db->query("SELECT * FROM game_content WHERE alias = '".$this->request->getQuery('article')."' LIMIT 1")->fetch();

		if (!isset($content['id']))
			$this->message('Страница не найдена!');

		$this->view->setVar('html', stripslashes($content['html']));

		$this->tag->setTitle($content['title']);
		$this->showTopPanel(false);
	}
}