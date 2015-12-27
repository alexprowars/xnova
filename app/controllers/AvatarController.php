<?php

namespace App\Controllers;

class AvatarController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function upload()
	{
		core::loadLib('upload');

		$upload = new \upload($_FILES['image']);

		if ($upload->uploaded)
		{
			$name = $this->user->getId().'_'.time().'.jpg';

			$upload->dir_auto_create = false;
			$upload->dir_auto_chmod = false;
			$upload->file_overwrite = true;
			$upload->file_max_size = 102400;
			$upload->mime_check = true;
			$upload->allowed = array('image/*');
			$upload->image_convert = 'jpg';
			$upload->image_resize = true;
			$upload->image_x = 128;
			$upload->image_y = 128;
			$upload->jpeg_quality = 90;
			$upload->file_new_name_body = $this->user->getId().'_'.time();

			$upload->Process('images/avatars/upload/');

			if ($upload->processed && file_exists(ROOT_DIR.'/images/avatars/upload/'.$name))
			{
				$this->db->query("UPDATE game_users_info SET image = '".$name."' WHERE id = " . $this->user->getId() . "");

				$this->message("Аватар успешно установлен.", "ОК", "?set=options", 3);
			}
			else
				$this->message($upload->error, "Ошибка", "?set=avatar", 3);

			$upload->Clean();
		}
	}
	
	public function indexAction ()
	{
		$html = '<center><form name="form2" enctype="multipart/form-data" method="post" action="?set=avatar&mode=upload">
				<table width=500><tr><td class=c>Загрузка аватара</td></tr>
				<tr><th>
					Картинки уменьшаются до размера 128 на 128 пикселей<br><br>
		            <input type="file" name="image" value="" />
		            <input type="submit" name="Submit" value="Загрузить" /></th></tr></table>
		        </form></center>';

		$this->tag->setTitle("Выбор аватара");
		$this->setContent($html);
		$this->showTopPanel(false);
	}
}

?>