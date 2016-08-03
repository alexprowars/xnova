<?php

namespace Admin\Controllers;

use Admin\Controller;

/**
 * @RoutePrefix("/admin")
 * @Route("/")
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
		return $this->response->redirect('admin/overview/');
	}
}