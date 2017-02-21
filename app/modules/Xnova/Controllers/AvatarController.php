<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Upload\Upload;
use Xnova\Controller;
use Xnova\Exceptions\RedirectException;

/**
 * @RoutePrefix("/avatar")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class AvatarController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		$this->showTopPanel(false);
	}

	public function uploadAction ()
	{
		if (!isset($_FILES['image']))
			return;

		$upload = new Upload($_FILES['image']);

		if ($upload->uploaded)
		{
			$name = $this->user->getId().'_'.time().'.jpg';

			$upload->dir_auto_create = false;
			$upload->dir_auto_chmod = false;
			$upload->file_overwrite = true;
			$upload->file_max_size = 102400;
			$upload->mime_check = true;
			$upload->allowed = ['image/*'];
			$upload->image_convert = 'jpg';
			$upload->image_resize = true;
			$upload->image_x = 200;
			$upload->image_y = 200;
			$upload->jpeg_quality = 90;
			$upload->file_new_name_body = $this->user->getId().'_'.time();

			$upload->process(ROOT_PATH.'/public/assets/avatars/');

			if ($upload->processed && file_exists(ROOT_PATH.'/public/assets/avatars/'.$name))
			{
				$this->db->query("UPDATE game_users_info SET image = '".$name."' WHERE id = " . $this->user->getId() . "");

				$upload->clean();

				throw new RedirectException("Аватар успешно установлен.", "ОК", "/options/", 3);
			}

			$upload->clean();

			throw new RedirectException($upload->error, "Ошибка", "/avatar/", 3);
		}
	}
	
	public function indexAction ()
	{
		$this->tag->setTitle("Выбор аватара");
	}
}