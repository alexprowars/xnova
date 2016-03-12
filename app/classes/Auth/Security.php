<?php
namespace App\Auth;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Mvc\User\Component;

/**
 * Class Security
 * @property \App\Auth\Auth auth
 * @property \Phalcon\Session\Bag persistent
 */
class Security extends Component
{
	/**
	 * @return AclList
	 */
	public function getAcl()
	{
		//if (!isset($this->persistent->acl))
		{
			$acl = new AclList();
			$acl->setDefaultAction(Acl::DENY);

			//Register roles
			$roles = [
				'users'  => new Role('Users'),
				'guests' => new Role('Guests')
			];

			foreach ($roles as $role)
			{
				$acl->addRole($role);
			}

			//Private area resources
			$privateResources = [
				'admin'		=> array('*'),
				'overview'	=> array('*'),
				'start'		=> array('*'),
				'alliance'	=> array('*'),
				'avatar'	=> array('*'),
				'banned'	=> array('*'),
				'buddy'		=> array('*'),
				'buildings'	=> array('*'),
				'calculate'	=> array('*'),
				'chat'		=> array('*'),
				'contact'	=> array('*'),
				'content'  	=> array('*'),
				'credits'  	=> array('*'),
				'fleet'  	=> array('*'),
				'galaxy'  	=> array('*'),
				'hall'  	=> array('*'),
				'imperium'  => array('*'),
				'info'  	=> array('*'),
				'jumpgate'  => array('*'),
				'log'  		=> array('*'),
				'logout'  	=> array('*'),
				'logs'  	=> array('*'),
				'merchant'  => array('*'),
				'messages'  => array('*'),
				'news'  	=> array('*'),
				'notes'  	=> array('*'),
				'officier'  => array('*'),
				'options'  	=> array('*'),
				'pay'  		=> array('*'),
				'phalanx'  	=> array('*'),
				'players'  	=> array('*'),
				'race'  	=> array('*'),
				'records'  	=> array('*'),
				'refers'  	=> array('*'),
				'resources' => array('*'),
				'rocket'  	=> array('*'),
				'rw'  		=> array('*'),
				'search'  	=> array('*'),
				'sim'  		=> array('*'),
				'stat'  	=> array('*'),
				'support'	=> array('*'),
				'tech'		=> array('*'),
				'tutorial'	=> array('*'),
				'git'		=> array('*'),
			];

			$publicResources = [
				'index'		=> array('*'),
				'error'		=> array('*'),
				'contact'	=> array('*'),
				'stat'		=> array('*'),
				'banned'	=> array('*'),
				'xnsim'		=> array('*'),
			];

			foreach ($privateResources as $resource => $actions)
			{
				$acl->addResource(new Resource($resource), $actions);
			}

			foreach ($publicResources as $resource => $actions)
			{
				$acl->addResource(new Resource($resource), $actions);
			}

			//Grant access to public areas to both users and guests
			foreach ($roles as $role)
			{
				foreach ($publicResources as $resource => $actions)
				{
					foreach ($actions as $action)
					{
						/**
						 * @var \Phalcon\Acl\Role $role
						 */
						$acl->allow($role->getName(), $resource, $action);
					}
				}
			}

			//Grant acess to private area to role Users
			foreach ($privateResources as $resource => $actions)
			{
				foreach ($actions as $action)
				{
					$acl->allow('Users', $resource, $action);
				}
			}

			//The acl is stored in session, APC would be useful here too
			$this->persistent->acl = $acl;
		}

		return $this->persistent->acl;
	}
	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 * @return bool
	 */
	public function beforeExecuteRoute (/** @noinspection PhpUnusedParameterInspection */Event $event, Dispatcher $dispatcher)
	{
		$auth = $this->auth->check();

		if (!$auth)
			$role = 'Guests';
		else
			$role = 'Users';

		if ($auth !== false)
			$this->getDI()->set('user', $auth);

		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$acl = $this->getAcl();
		$allowed = $acl->isAllowed($role, $controller, $action);

		if ($allowed != Acl::ALLOW)
		{
			$this->response->redirect('');
			$this->response->send();
			die();
		}

		return true;
	}
}