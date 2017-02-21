<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\ErrorException;

/**
 * @RoutePrefix("/content")
 * @Route("/")
 */
class ContentController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	/**
	 * @Route("/{article:[a-zA-Z0-9]+}{params:(/.*)*}")
	 */
	function indexAction ()
	{
		if (!$this->request->getQuery('article'))
			throw new ErrorException('Страница не найдена!');

		$content = $this->db->query("SELECT * FROM game_content WHERE alias = '".$this->request->getQuery('article')."' LIMIT 1")->fetch();

		if (!isset($content['id']))
			throw new ErrorException('Страница не найдена!');

		$this->view->setVar('html', stripslashes($content['html']));

		$this->tag->setTitle($content['title']);
		$this->showTopPanel(false);
	}
}