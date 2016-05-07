<?php
namespace Xnova\Admin\Controllers;

class ErrorController extends Application
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
	{

	}

	public function notFoundAction ()
	{
		$this->message('Запрашиваемая вами страница не найдена', _getText('sys_noaccess'));
	}
}

?>