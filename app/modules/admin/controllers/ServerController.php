<?php
namespace Xnova\Admin\Controllers;

class ServerController extends Application
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		$this->tag->setTitle('Серверное окружение');
	}
}

?>