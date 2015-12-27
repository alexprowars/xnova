<?php

namespace App\Controllers;

use Xcms\cms;
use Xcms\core;
use Xcms\db;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

/**
 * Class showAdminPage
 * $Revision: 325 $
 * $Date: 2014-02-28 01:30:27 +0400 (Пт, 28 фев 2014) $
 */
class AdminController extends ApplicationController
{
	private $modules = array();

	function __construct ()
	{
		core::setConfig('gameTemplate', 'admin');

		parent::__construct();
		
		strings::includeLang('admin');

		$result = db::query("SELECT m.id, m.alias, m.name, r.right_id FROM game_cms_modules m LEFT JOIN game_cms_rights r ON r.module_id = m.id AND r.group_id = ".user::get()->data['group_id']." AND right_id != '0' WHERE m.is_admin = '1' AND m.active = '1'");

		while ($r = db::fetch($result))
		{
			$this->modules[$r['alias']] = array
			(
				'id' 	=> $r['id'],
				'alias'	=> $r['alias'],
				'name' 	=> $r['name'],
				'right' => user::get()->isAdmin() ? 2 : (!$r['right_id'] ? 0 : $r['right_id'])
			);
		}

		$menu = cms::getMenu(1, 2);

		foreach ($menu AS $i => $item)
		{
			if (!isset($this->modules[$item['alias']]) || !$this->modules[$item['alias']]['right'])
				unset($menu[$i]);
		}

		$this->globals('menu', $menu);
	}
	
	public function show ()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$error = '';

		if ($this->mode == core::getConfig('defaultAction', 'show'))
		{
			$default = $this->modules;
			$default = array_shift($default);

			$this->mode = $default['alias'];
		}

		if (isset($this->modules[$this->mode]) && $this->modules[$this->mode]['right'] > 0)
		{
			if (file_exists(ROOT_DIR.APP_PATH."controllers/admin/".$this->modules[$this->mode]['alias'].".php"))
				require(ROOT_DIR.APP_PATH."controllers/admin/".$this->modules[$this->mode]['alias'].".php");
			else
				$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
		}
		else
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>