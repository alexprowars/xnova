<?php

namespace Friday\Core\Models;

use Phalcon\Http\Request\File;
use Phalcon\Mvc\Model;

class User extends Model
{
	public $id;
	public $email;
	public $password;
	public $photo;
	public $name;
	public $last_name;
	public $second_name;
	private $groupsId = [];

	public $photoDir = '/upload/photos/';

	public function initialize ()
	{
		$this->hasMany('id', __NAMESPACE__.'\UserGroup', 'user_id', ['alias' => 'groups']);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getSource()
	{
		return DB_PREFIX."users";
	}

	/**
	 * @param mixed $parameters
	 * @return UserGroup[]
	 */
	public function getGroups ($parameters = null)
	{
		return $this->getRelated('groups', $parameters);
	}

	public function getGroupsId ()
	{
		if (!empty($this->groupsId))
			return $this->groupsId;

		if ($this->getDI()->has('session'))
		{
			/**
			 * @var $session \Phalcon\Session\AdapterInterface
			 */
			$session = $this->getDI()->getShared('session');

			if ($session->has('GROUPS_ID_'.$this->id))
			{
				$this->groupsId = $session->get('GROUPS_ID_'.$this->id);

				if (!is_array($this->groupsId))
					$this->groupsId = [];

				return $this->groupsId;
			}
		}

		$groups = $this->getGroups();

		foreach ($groups as $group)
			$this->groupsId[] = $group->group_id;

		if (isset($session))
			$session->set('GROUPS_ID_'.$this->id, $this->groupsId);

		return $this->groupsId;
	}

	public function getFullName ()
	{
		return trim($this->name.' '.$this->second_name.' '.$this->last_name);
	}

	public function getPhoto ()
	{
		if ($this->photo == '')
			return false;

		return $this->photoDir.$this->photo;
	}

	public function uploadPhoto (File $file)
	{
		if (!$file->isUploadedFile())
			return;

		$path = '/public'.$this->photoDir;

		$prefix = mb_substr(md5($file->getName().time()), 0, 3);

		if (!is_dir(ROOT_PATH.$path.$prefix))
			mkdir(ROOT_PATH.$path.$prefix);

		$oldPhoto = $this->photo;

		if ($file->moveTo(ROOT_PATH.$path.$prefix.'/'.md5($file->getName()).'.'.$file->getExtension()))
			$this->photo = $prefix.'/'.md5($file->getName()).'.'.$file->getExtension();
		else
			$this->photo = '';

		if ($oldPhoto != '')
			unlink(ROOT_PATH.$path.$oldPhoto);
	}

	public function deletePhoto ()
	{
		if ($this->photo == '')
			return;

		if (file_exists(ROOT_PATH.'/public'.$this->photoDir.$this->photo))
			unlink(ROOT_PATH.'/public'.$this->photoDir.$this->photo);

		$this->photo = '';
	}

	public function afterDelete ()
	{
		$this->deletePhoto();
	}
}