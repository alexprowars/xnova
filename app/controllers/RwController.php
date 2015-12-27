<?php

namespace App\Controllers;

class RwController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		global $session;

		include(ROOT_DIR.APP_PATH."functions/formatCR.php");
		
		$raportrow = $this->db->query("SELECT * FROM game_rw WHERE `id` = '" . intval($_GET['r']) . "';")->fetch();
		
		if (!isset($raportrow['id']))
			$this->message('Данный боевой отчет удалён с сервера', 'Ошибка', '', 0, false);
		
		$user_list = json_decode($raportrow['id_users'], true);
		
		if (isset($raportrow['id']) && !$this->user->isAdmin() && (!isset($_GET['k']) ||  md5('xnovasuka' . $raportrow['id']) != $_GET['k']))
			$this->message('Не правильный ключ', 'Ошибка', '', 0, false);
		elseif (!in_array($this->user->id, $user_list) && !$this->user->isAdmin())
			$this->message('Вы не можете просматривать этот боевой доклад', 'Ошибка', '', 0, false);
		else
		{
			if ($this->page->ajax && $session->isAuthorized())
			{
				$Page = "";
		
				if ($user_list[0] == $this->user->id && $raportrow['no_contact'] == 1 && !$this->user->isAdmin())
				{
					$Page .= "Контакт с вашим флотом потерян.<br>(Ваш флот был уничтожен в первой волне атаки.)";
				}
				else
				{
					$result = json_decode($raportrow['raport'], true);

					$formatted_cr = formatCREx($result[0], $result[1], $result[2], $result[3], $result[4], $result[5]);
					$Page .= $formatted_cr['html'];
		
					$Page .= '<script>$(function(){$(\'#raportRaw\').multiAccordion({active: ['.(count($result[0]['rw']) - 1).']})});;</script>';
				}
		
				$Page .= "<div class='separator'></div>ID боевого доклада: <a href=\"?set=log&mode=new&save=" . md5('xnovasuka' . $raportrow['id']) . $raportrow['id'] . "\"><font color=red>" . md5('xnovasuka' . $raportrow['id']) . $raportrow['id'] . "</font></a>";
		
				if (core::getConfig('gameTemplate') == 'main')
				{
					$Page .= '<div class="separator"></div><a data-link="1" target="_blank" href="?set=rw&r='.$_GET['r'].'&k='.$_GET['k'].'">Полная версия боя</a>';
				}

				$this->tag->setTitle('Боевой доклад');
				$this->setContent($Page);
				$this->showTopPanel(false);
			}
			else
			{
				$result = json_decode($raportrow['raport'], true);

				if (isset($result[0]['version']) && $result[0]['version'] == 2)
				{
					include(ROOT_DIR.APP_PATH.'functions/formatCombatReport.php');
				}

				$Page = "<html><head><title>Боевой доклад</title>";

				if (isset($result[0]['version']) && $result[0]['version'] == 2)
					$Page .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".DPATH."report_v2.css?v=".substr(md5(VERSION), 0, 3)."\">";
				else
					$Page .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".DPATH."report.css?v=".substr(md5(VERSION), 0, 3)."\">";

				$Page .= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />";
				$Page .= "</head><body><script>function show(id){if(document.getElementById(id).style.display==\"block\")document.getElementById(id).style.display=\"none\"; else document.getElementById(id).style.display=\"block\";}</script>";
				$Page .= "<table width=\"99%\"><tr><td><center>";
		
				if ($user_list[0] == $this->user->id && $raportrow['no_contact'] == 1 && !$this->user->isAdmin())
				{
					$Page .= "Контакт с вашим флотом потерян.<br>(Ваш флот был уничтожен в первой волне атаки.)";
				}
				else
				{
					if (isset($result[0]['version']) && $result[0]['version'] == 2)
						$formatted_cr = formatCombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);
					else
						$formatted_cr = formatCR($result[0], $result[1], $result[2], $result[3], $result[4], $result[5]);

					$Page .= $formatted_cr['html'];
				}
		
				$Page .= "</center></td></tr><tr align=center><td>ID боевого доклада: <a href=\"?set=log&mode=new&save=" . md5('xnovasuka' . $raportrow['id']) . $raportrow['id'] . "\"><font color=red>" . md5('xnovasuka' . $raportrow['id']) . $raportrow['id'] . "</font></a></td></tr>";
				$Page .= "</table></body></html>";
		
				echo $Page;
			}
		}
	}
}

?>