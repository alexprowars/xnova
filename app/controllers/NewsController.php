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
	
	public function indexAction ()
	{
		$news = [];

		foreach (_getText('news') as $a => $b)
		{
			$news[] = [$a, nl2br($b)];
		}

		$this->view->setVar('parse', $news);

		exec('git rev-parse --verify HEAD 2> /dev/null', $output);

		$lastCommit = $output[0];

		$this->view->setVar('lastCommit', $lastCommit);

		$this->tag->setTitle('Новости');
		$this->showTopPanel(false);
	}
}

?>