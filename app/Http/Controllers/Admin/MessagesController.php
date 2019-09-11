<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Helpers;

class MessagesController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'messages',
			'title' => 'Сообщения',
			'icon'	=> 'email',
			'sort'	=> 170
		]];
	}

	public function index ()
	{
		$parse = [];

		$Prev = !empty($_POST['prev']);
		$Next = !empty($_POST['next']);
		$DelSel = !empty($_POST['delsel']);
		$DelDat = !empty($_POST['deldat']);
		$CurrPage = (!empty($_POST['curr'])) ? intval($_POST['curr']) : 1;
		$Selected = isset($_POST['type']) ? intval($_POST['type']) : 1;
		$SelPage = (int) $this->request->get('p', 'int', 1);

		if ($Selected == 6)
			$Selected = 0;

		$parse['types'] = [1, 2, 3, 4, 5, 0];

		if ($this->user->authlevel == 1)
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
		elseif ($DelSel == true && $this->user->authlevel > 1)
		{
			foreach ($_POST['sele_mes'] as $MessId => $Value)
			{
				if ($Value = "on")
					$this->db->query("DELETE FROM messages WHERE id = '" . $MessId . "';");
			}
		}
		elseif ($DelDat == true && $this->user->authlevel > 1)
		{
			$SelDay 	= intval($_POST['selday']);
			$SelMonth 	= intval($_POST['selmonth']);
			$SelYear 	= intval($_POST['selyear']);

			$LimitDate = mktime(0, 0, 0, $SelMonth, $SelDay, $SelYear);

			if ($LimitDate != false)
			{
				$this->db->query("DELETE FROM messages WHERE time <= '" . $LimitDate . "';");
				$this->db->query("DELETE FROM rw WHERE time <= '" . $LimitDate . "';");
			}
		}

		$Mess = $this->db->query("SELECT COUNT(*) AS max FROM messages WHERE type = '" . $Selected . "';")->fetch();
		$MaxPage = ceil(($Mess['max'] / 25));

		$parse['mlst_data_page'] = $ViewPage;
		$parse['mlst_data_pagemax'] = $MaxPage;
		$parse['mlst_data_sele'] = $Selected;

		if (isset($_POST['userid']) && $_POST['userid'] != "")
		{
			$userid = " AND user_id = " . intval($_POST['userid']) . "";
			$parse['userid'] = intval($_POST['userid']);
		}
		elseif (isset($_POST['userid_s']) && $_POST['userid_s'] != "")
		{
			$userid = " AND from_id = " . intval($_POST['userid_s']) . "";
			$parse['userid_s'] = intval($_POST['userid_s']);
		}
		else
			$userid = "";

		$Messages = $this->db->query("SELECT m.*, u.username FROM messages m LEFT JOIN users u ON u.id = m.user_id WHERE m.type = '" . $Selected . "' " . $userid . " ORDER BY m.time DESC LIMIT " . (($ViewPage - 1) * 25) . ",25;");

		$parse['mlst_data_rows'] = [];

		while ($row = $Messages->fetch())
		{
			$row['text'] = str_replace('/admin', '', str_replace('#BASEPATH#', $this->url->getBaseUri(), $row['text']));

			$bloc['mlst_id'] = $row['id'];
			$bloc['mlst_from'] = $row['from_id'];
			$bloc['mlst_to'] = $row['username'] . " ID:" . $row['user_id'];
			$bloc['mlst_text'] = stripslashes(nl2br($row['text']));
			$bloc['mlst_time'] = date("d.m.Y H:i:s", $row['time']);

			$parse['mlst_data_rows'][] = $bloc;
		}

		if (isset($_POST['delit']) && $this->user->authlevel > 1)
		{
			$this->db->query("DELETE FROM messages WHERE id = '" . $_POST['delit'] . "';");
			$this->message(_getText('mlst_mess_del') . " ( " . $_POST['delit'] . " )", _getText('mlst_title'), "/messages/", 3);
		}

		$pagination = Helpers::pagination($Mess['max'], 25, '/admin/messages/', $ViewPage);

		View::share('title', __('admin.mlst_title'));

		return view('admin.messages', ['parse' => $parse, 'pagination' => $pagination]);
	}
}