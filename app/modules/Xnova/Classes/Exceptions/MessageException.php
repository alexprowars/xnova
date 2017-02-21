<?php

namespace Xnova\Exceptions;

use Phalcon\Di;
use Phalcon\Mvc\View;

class MessageException extends \Exception
{
	protected $title = '';
	protected $url = '';
	protected $timeout = 5;
	protected $showLeft = true;

	public function __construct ($message, $title = '', $url = '', $timeout = 5, $showLeft = true)
	{
		if (!$title)
			$title = 'Ошибка';

		$this->title = $title;
		$this->url = $url;
		$this->timeout = (int) $timeout;
		$this->showLeft = $showLeft;

		parent::__construct($message, 0);
	}

	public function __toString()
	{
		/** @var $app \Friday\Core\Application */
		$app = Di::getDefault()->getShared('app');

		$app->view->pick('shared/message');
		$app->view->setVar('text', $this->getMessage());
		$app->view->setVar('title', $this->title);

		if ($this->url != '')
			$app->view->setVar('destination', $app->url->getBaseUri().ltrim($this->url, '/'));
		else
			$app->view->setVar('destination', '');

		$app->view->setVar('time', $this->timeout);

		$app->tag->setTitle(($this->title ? strip_tags($this->title) : 'Сообщение'));

		/** @var \Xnova\Controller $controller */
		$controller = $app->dispatcher->getLastController();

		$controller->showTopPanel(false);
		$controller->showLeftPanel($this->showLeft);
		$controller->afterExecuteRoute();

		$app->view->setRenderLevel(View::LEVEL_MAIN_LAYOUT);

		$app->view->start();
		$app->view->render('error', 'index');
		$app->view->finish();

		if ($app->request->isAjax())
		{
			$app->response->setJsonContent(
			[
				'status' 	=> $app->game->getRequestStatus(),
				'message' 	=> $app->game->getRequestMessage(),
				'html' 		=> str_replace("\t", ' ', $app->view->getContent()),
				'data' 		=> $app->game->getRequestData()
			]);

			$app->response->setContentType('text/json', 'utf8');
			$app->response->send();

			return '';
		}
		else
	   		return $app->view->getContent();
	}
}