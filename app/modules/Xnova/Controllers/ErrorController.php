<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controller;

class ErrorController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if (!$this->auth->isAuthorized())
		{
			$css = $this->assets->collection('css');
			
			$css->addCss('assets/css/bootstrap.css');
			$css->addCss('assets/css/style.css');
		}
	}

	public function indexAction()
	{

	}

    public function notFoundAction()
    {
		file_put_contents(APP_PATH.'/php_errors.log', "\n\n".print_r($_SERVER, true)."\n\n", FILE_APPEND);

		$this->view->setMainView('404');
        $this->response->setStatusCode(404, 'Not Found');
    }
}