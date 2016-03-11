<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Lang;

class Messages
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 1)
		{
			Lang::includeLang('admin/messagelist');

			$parse = [];

			$Prev = !empty($_POST['prev']);
			$Next = !empty($_POST['next']);
			$DelSel = !empty($_POST['delsel']);
			$DelDat = !empty($_POST['deldat']);
			$CurrPage = (!empty($_POST['curr'])) ? intval($_POST['curr']) : 1;
			$Selected = isset($_POST['type']) ? intval($_POST['type']) : 1;
			$SelPage = @$_POST['page'];

			if ($Selected == 6)
				$Selected = 0;

			$parse['types'] = [1, 2, 3, 4, 5, 0];

			if ($controller->user->authlevel == 1)
				$parse['types'] = [3, 4, 5];

			if (!in_array($Selected, $parse['types']))
			{
				$t = $parse['types'];

				$Selected = array_shift($t);

				unset($t);
			}

			$ViewPage = (!empty($SelPage)) ? $SelPage : 1;

			if ($Prev == true)
			{
				$CurrPage -= 1;

				if ($CurrPage >= 1)
					$ViewPage = $CurrPage;
				else
					$ViewPage = 1;
			}
			elseif ($Next == true)
			{
				$CurrPage += 1;

				$ViewPage = $CurrPage;
			}
			elseif ($DelSel == true && $controller->user->authlevel > 1)
			{
				foreach ($_POST['sele_mes'] as $MessId => $Value)
				{
					if ($Value = "on")
						$controller->db->query("DELETE FROM game_messages WHERE `id` = '" . $MessId . "';");
				}
			}
			elseif ($DelDat == true && $controller->user->authlevel > 1)
			{
				$SelDay 	= intval($_POST['selday']);
				$SelMonth 	= intval($_POST['selmonth']);
				$SelYear 	= intval($_POST['selyear']);

				$LimitDate = mktime(0, 0, 0, $SelMonth, $SelDay, $SelYear);

				if ($LimitDate != false)
				{
					$controller->db->query("DELETE FROM game_messages WHERE `time` <= '" . $LimitDate . "';");
					$controller->db->query("DELETE FROM game_rw WHERE `time` <= '" . $LimitDate . "';");
				}
			}

			$Mess = $controller->db->query("SELECT COUNT(*) AS `max` FROM game_messages WHERE `type` = '" . $Selected . "';")->fetch();
			$MaxPage = ceil(($Mess['max'] / 25));

			$parse['mlst_data_page'] = $ViewPage;
			$parse['mlst_data_pagemax'] = $MaxPage;
			$parse['mlst_data_sele'] = $Selected;

			if (isset($_POST['userid']) && $_POST['userid'] != "")
			{
				$userid = " AND owner = " . intval($_POST['userid']) . "";
				$parse['userid'] = intval($_POST['userid']);
			}
			elseif (isset($_POST['userid_s']) && $_POST['userid_s'] != "")
			{
				$userid = " AND sender = " . intval($_POST['userid_s']) . "";
				$parse['userid_s'] = intval($_POST['userid_s']);
			}
			else
				$userid = "";

			$Messages = $controller->db->query("SELECT m.*, u.username FROM game_messages m LEFT JOIN game_users u ON u.id = m.owner WHERE m.`type` = '" . $Selected . "' " . $userid . " ORDER BY m.`time` DESC LIMIT " . (($ViewPage - 1) * 25) . ",25;");

			$parse['mlst_data_rows'] = [];

			while ($row = $Messages->fetch())
			{
				$bloc['mlst_id'] = $row['id'];
				$bloc['mlst_from'] = $row['from'];
				$bloc['mlst_to'] = $row['username'] . " ID:" . $row['owner'];
				$bloc['mlst_text'] = $row['text'];
				$bloc['mlst_time'] = date("d.m.Y H:i:s", $row['time']);

				$parse['mlst_data_rows'][] = $bloc;
			}

			if (isset($_POST['delit']) && $controller->user->authlevel > 1)
			{
				$controller->db->query("DELETE FROM game_messages WHERE `id` = '" . $_POST['delit'] . "';");
				$controller->message(_getText('mlst_mess_del') . " ( " . $_POST['delit'] . " )", _getText('mlst_title'), "?set=admin&mode=messagelist", 3);
			}

			$controller->view->pick('admin/messagelist');
			$controller->view->setVar('parse', $parse);
			$controller->tag->setTitle(_getText('mlst_title'));
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>