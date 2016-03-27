<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;

/**
 * Class showAdminPage
 * $Revision: 325 $
 * $Date: 2014-02-28 01:30:27 +0400 (Пт, 28 фев 2014) $
 */
class AdminController extends ApplicationController
{
	private $modules = [];
	private $mode = '';

	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		Lang::includeLang('admin');

		$result = $this->db->query("SELECT m.id, m.alias, m.name, r.right_id FROM game_cms_modules m LEFT JOIN game_cms_rights r ON r.module_id = m.id AND r.group_id = ".$this->user->group_id." AND right_id != '0' WHERE m.is_admin = '1' AND m.active = '1'");

		while ($r = $result->fetch())
		{
			$this->modules[mb_strtolower($r['alias'], 'utf-8')] =
			[
				'id' 	=> $r['id'],
				'alias'	=> $r['alias'],
				'name' 	=> $r['name'],
				'right' => $this->user->isAdmin() ? 2 : (!$r['right_id'] ? 0 : $r['right_id'])
			];
		}

		$menu = $this->getMenu(1, 2);

		foreach ($menu AS $i => $item)
		{
			if (!isset($this->modules[$item['alias']]) || !$this->modules[$item['alias']]['right'])
				unset($menu[$i]);
		}

		$this->mode = $this->request->getQuery('set', 'string', 'overview');

		$this->view->setVar('menu', $menu);
		$this->view->setVar('mode', $this->mode);
		$this->view->setMainView('admin');
		$this->showTopPanel(false);
	}
	
	public function indexAction ()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$error = '';

		if ($this->mode == 'index')
		{
			$default = $this->modules;
			$default = array_shift($default);

			$this->mode = mb_strtolower($default['alias'], 'utf-8');
		}

		if (isset($this->modules[$this->mode]) && $this->modules[$this->mode]['right'] > 0)
		{
			if (class_exists("App\\Controllers\\Admin\\".$this->modules[$this->mode]['alias'].""))
			{
				$class = "App\\Controllers\\Admin\\".$this->modules[$this->mode]['alias'];
				$class = new $class();

				$class->show($this);
			}
			else
				$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
		}
		else
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function getMenu ($parent_id, $lvl = 1, $all = false)
	{
		$array = [];

		if ($lvl > 0)
		{
			$childrens = $this->db->extractResult($this->db->query("SELECT id, name, alias, icon, image, active FROM game_cms_menu WHERE parent_id = ".intval($parent_id)." ".($all ? '' : "AND active = '1'")." ORDER BY priority ASC"));

			if (count($childrens) > 0)
			{
				foreach ($childrens AS $children)
				{
					$array[] = [
						'id' 		=> $children['id'],
						'alias' 	=> mb_strtolower($children['alias'], 'utf-8'),
						'name' 		=> $children['name'],
						'children' 	=> ($lvl > 1) ? $this->getMenu($children['id'], ($lvl - 1), $all) : [],
						'active' 	=> $children['active'],
						'icon' 		=> $children['icon'],
						'image' 	=> $children['image']
					];
				}
			}
		}

		return $array;
	}
}