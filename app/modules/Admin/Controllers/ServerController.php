<?php

namespace Admin\Controllers;

use Admin\Controller;

/**
 * @RoutePrefix("/admin/server")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ServerController extends Controller
{
	const CODE = 'server';

	public function initialize ()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'server',
			'title' => 'Информация',
			'icon'	=> 'info',
			'sort'	=> 30
		]];
	}

	public function indexAction ()
	{
		$this->tag->setTitle('Серверное окружение');
	}
}