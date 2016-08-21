<?php

namespace Friday\Core;

use Friday\Core\Models\Group;
use Xnova\Models\User;
use Phalcon\Di\Injectable;

class Access extends Injectable
{
	private $_accessList;
	private $_isAdmin;

	/**
	 * @var User
	 */
	private $_user;

	public function __construct ($user = null)
	{
		if (is_object($user) && $user instanceof User)
			$this->_user = $user;
		elseif (is_numeric($user))
		{
			$obj = User::findFirst($user);

			if ($obj)
				$this->_user = $obj;
		}

		if (!isset($this->_user) && $this->getDI()->has('user'))
			$this->_user = $this->getDI()->getShared('user');
	}

	private function prepare ($moduleId = 'core')
	{
		if (!isset($this->_accessList[$moduleId]))
			$this->_accessList[$moduleId] = [];

		if (isset($this->_user))
		{
			$groupsId = $this->_user->getGroupsId();
			$groupsId[] = Group::ROLE_USER;
		}

		$groupsId[] = Group::ROLE_ANONYM;

		$groupsId = array_unique($groupsId);

		/**
		 * @var $manager \Phalcon\Mvc\Model\Manager
		 */
		$manager = $this->getDI()->getShared("modelsManager");

		$items = $manager->createBuilder()
			->columns(['access.id', 'access.code'])
			->addFrom('\Friday\Core\Models\Access', 'access')
			->addFrom('\Friday\Core\Models\GroupAccess', 'groups')
			->andWhere("groups.group_id IN (".implode(',', $groupsId).")")
			->andWhere("access.id = groups.access_id")
			->andWhere("access.module = '".$moduleId."'")
			->getQuery()
			->execute();

		/**
		 * @var $items \Friday\Core\Models\Access
		 */
		foreach ($items as $item)
		{
			$this->_accessList[$moduleId][$item->code] = $item->id;
		}
	}

	public function isAdmin ()
	{
		if (isset($this->_isAdmin))
			return $this->_isAdmin;

		$this->_isAdmin = false;

		if (isset($this->_user))
			$this->_isAdmin = $this->_user->isAdmin();

		return $this->_isAdmin;
	}

	public function has ($role, $module)
	{
		$module = mb_strtolower($module);
		$role = mb_strtolower($role);

		if ($this->isAdmin())
			return true;

		if (!isset($this->_accessList[$module]))
			$this->prepare($module);

		if (isset($this->_accessList[$module][$role]))
			return true;

		return false;
	}

	public function canReadController ($controller, $module)
	{
		return ($this->has('controller_read::'.$controller, $module) || $this->has('controller_write::'.$controller, $module));
	}

	public function canWriteController ($controller, $module)
	{
		return $this->has('controller_write::'.$controller, $module);
	}

	public function hasAccess ($module)
	{
		return $this->has('access', $module);
	}
}