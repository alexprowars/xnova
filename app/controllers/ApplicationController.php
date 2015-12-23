<?php
namespace App\Controllers;

use App\Lang;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;

class ApplicationController extends Controller
{
	public function initialize()
	{
		Lang::setLang($this->config->app->language);

		if ($this->request->isAjax())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' | ');
			$this->tag->setTitle($this->config->app->name);
	        $this->tag->setDoctype(Tag::HTML5);
		}

		if ($this->auth->isAuthorized())
		{

		}

		return true;
	}

	public function message ($text, $title = '')
	{
		$this->view->pick('shared/message');
		$this->view->setVar('text', $text);
		$this->view->setVar('title', $title);
		$this->view->start();

		return true;
	}
}

?>