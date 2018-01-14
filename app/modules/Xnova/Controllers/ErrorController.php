<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Modules;
use Xnova\Controller;

/**
 * @RoutePrefix("/error")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class ErrorController extends Controller
{
	public function initialize ()
	{
		Modules::init('xnova');

		parent::initialize();

		if (!$this->auth->isAuthorized())
		{
			$this->assets->addCss('assets/css/bootstrap.css');
			$this->assets->addCss('assets/css/style.css');
		}
	}

	public function indexAction()
	{

	}

    public function notFoundAction()
    {
		file_put_contents(ROOT_PATH.'/php_errors.log', "\n\n".print_r($_SERVER, true)."\n\n".print_r($_REQUEST, true)."\n\n", FILE_APPEND);

		$this->view->setMainView('404');
        $this->response->setStatusCode(404, 'Not Found');
    }
}