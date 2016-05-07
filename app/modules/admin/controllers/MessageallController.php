<?php
namespace Xnova\Admin\Controllers;

use App\Models\User;

class MessageToAllController extends Application
{
	public function indexAction ()
	{
		if ($this->user->authlevel < 1)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		if (isset($_POST["tresc"]))
		{
			$kolor = '';

			if ($this->user->authlevel == 3)
			{
				$kolor = 'yellow';
			}
			elseif ($this->user->authlevel == 1)
			{
				$kolor = 'skyblue';
			}
			elseif ($this->user->authlevel == 2)
			{
				$kolor = 'yellow';
			}

			if ((isset($_POST["tresc"]) && $_POST["tresc"] != '') && (isset($_POST["temat"]) && $_POST["temat"] != ''))
			{
				$sq = $this->db->query("SELECT `id` FROM game_users");

				$Time = time();

				$From 		= "<font color=\"" . $kolor . "\">Информационное сообщение (".$this->user->username.")</font>";
				$Message 	= $_POST['tresc'];

				while ($u = $sq->fetch())
				{
					User::sendMessage($u['id'], false, $Time, 1, $From, $Message);
				}

				$this->message("<font color=\"lime\">Сообщение успешно отправлено всем игрокам!</font>", "Выполнено", "?set=admin&mode=messall", 3);
			}
			else
				$this->message("<font color=\"red\">Не все поля заполнены!</font>", "Ошибка", "?set=admin&mode=messall", 3);
		}
		else
		{
			$this->tag->setTitle('Рассылка');
		}
	}
}

?>