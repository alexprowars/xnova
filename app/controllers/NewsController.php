<?php

namespace App\Controllers;

use App\Lang;

class NewsController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('news');
	}
	
	public function show ()
	{
		$news = array();

		foreach (_getText('news') as $a => $b)
		{
			$news[] = array($a, nl2br($b));
		}

		$this->view->pick('news');
		$this->view->setVar('parse', $news);

		$this->tag->setTitle('Новости');
		$this->showTopPanel(false);
		$this->showLeftPanel(isset($this->user->id));
	}
}

?>