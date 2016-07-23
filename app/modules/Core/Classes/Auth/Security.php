<?php

namespace Friday\Core\Auth;

use Friday\Core\Access;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Component;

/**
 * @property \Friday\Core\Auth\Auth auth
 */
class Security extends Component
{
	/**
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 * @return bool
	 */
	public function beforeExecuteRoute (/** @noinspection PhpUnusedParameterInspection */Event $event, Dispatcher $dispatcher)
	{
		$auth = $this->auth->check();

		if (!$auth)
			$role = 'Guest';
		else
			$role = 'User';

		if ($auth !== false)
			$this->getDI()->set('user', $auth, true);

		$this->getDI()->set('access', new Access(), true);

		$controllerName = $dispatcher->getControllerClass();

		$annotations = $this->annotations->get($controllerName);

		if (($annotations->getClassAnnotations()->has('Private') && $role == 'User') || !$annotations->getClassAnnotations()->has('Private'))
			return true;
		else
		{
			$actions = $annotations->getMethodsAnnotations();

			if ($actions)
			{
				$actionName = $dispatcher->getActiveMethod();

				if (isset($actions[$actionName]))
				{
					if (($actions[$actionName]->has('Private') && $role == 'User') || !$actions[$actionName]->has('Private'))
						return true;
				}
			}
		}

		$this->response->redirect('')->send();
		die();
	}
}