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
	}

	public function indexAction()
	{

	}
}