<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Request;

/**
 * @RoutePrefix("/content")
 * @Route("/")
 */
class ContentController extends Controller
{
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

		Request::addData('page', [
			'html' => stripslashes($content['html'])
		]);

		$this->tag->setTitle($content['title']);
		$this->showTopPanel(false);
	}
}