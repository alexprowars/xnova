<?php

namespace Admin\Controllers;

use Admin\Controller;

/**
 * @RoutePrefix("/admin")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class IndexController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
	{

	}
}