<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Models\User;

class MessageToAll
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel < 1)
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		if (isset($_POST["tresc"]))
		{
			$kolor = '';

			if ($controller->user->authlevel == 3)
			{
				$kolor = 'yellow';
			}
			elseif ($controller->user->authlevel == 1)
			{
				$kolor = 'skyblue';
			}
			elseif ($controller->user->authlevel == 2)
			{
				$kolor = 'yellow';
			}

			if ((isset($_POST["tresc"]) && $_POST["tresc"] != '') && (isset($_POST["temat"]) && $_POST["temat"] != ''))
			{
				$sq = $controller->db->query("SELECT `id` FROM game_users");

				$Time = time();

				$From 		= "<font color=\"" . $kolor . "\">Информационное сообщение (".$controller->user->username.")</font>";
				$Message 	= $_POST['tresc'];

				while ($u = $sq->fetch())
				{
					User::sendMessage($u['id'], false, $Time, 1, $From, $Message);
				}

				$controller->message("<font color=\"lime\">Сообщение успешно отправлено всем игрокам!</font>", "Выполнено", "?set=admin&mode=messall", 3);
			}
			else
				$controller->message("<font color=\"red\">Не все поля заполнены!</font>", "Ошибка", "?set=admin&mode=messall", 3);
		}
		else
		{
			$controller->view->pick('admin/messtoall');
			$controller->tag->setTitle('Рассылка');
		}
	}
}

?>