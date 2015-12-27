<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;

class Shortcut
{
	public function show (FleetController $controller)
	{
		$html = '';

		$inf = $controller->db->query("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $controller->user->id . ";")->fetch();

		if (isset($_GET['mode']))
		{
			if ($_POST)
			{
				if ($_POST["n"] == "" || !preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $_POST["n"]))
					$_POST["n"] = "Планета";

				$g = intval($_POST['g']);
				$s = intval($_POST['s']);
				$i = intval($_POST['p']);
				$t = intval($_POST['t']);

				if ($g < 1 || $g > $controller->config->game->maxGalaxyInWorld)
					$g = 1;
				if ($s < 1 || $s > $controller->config->game->maxSystemInGalaxy)
					$s = 1;
				if ($i < 1 || $i > $controller->config->game->maxPlanetInSystem)
					$i = 1;
				if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
					$t = 1;

				$inf['fleet_shortcut'] .= strip_tags(str_replace(',', '', $_POST['n'])) . "," . $g . "," . $s . "," . $i . "," . $t . "\r\n";

				$controller->db->updateAsDict('game_users_info', ['fleet_shortcut' => $inf['fleet_shortcut']], 'id = '.$controller->user->getId());

				if (isset($_SESSION['fleet_shortcut']))
					unset($_SESSION['fleet_shortcut']);

				$controller->message("Ссылка на планету добавлена!", "Добавление ссылки", "?set=fleet&page=shortcut");
			}

			$g = $controller->request->getPost('g', 'int', 0);
			$s = $controller->request->getPost('s', 'int', 0);
			$p = $controller->request->getPost('p', 'int', 0);
			$t = $controller->request->getPost('t', 'int', 0);

			if ($g < 1 || $g > $controller->config->game->maxGalaxyInWorld)
				$g = 1;
			if ($s < 1 || $s > $controller->config->game->maxSystemInGalaxy)
				$s = 1;
			if ($p < 1 || $p > $controller->config->game->maxPlanetInSystem)
				$p = 1;
			if ($t != 1 && $t != 2 && $t != 3 && $t != 5)
				$t = 1;

			$controller->view->pick('fleet/shortcut_new');
			$controller->view->setVar('g', $g);
			$controller->view->setVar('s', $s);
			$controller->view->setVar('i', $p);
			$controller->view->setVar('t', $t);
		}
		elseif (isset($_GET['a']))
		{
			if ($_POST)
			{
				$a = intval($_POST['a']);
				$scarray = explode("\r\n", $inf['fleet_shortcut']);

				if (isset($_POST["delete"]))
				{
					unset($scarray[$a]);
					$inf['fleet_shortcut'] = implode("\r\n", $scarray);

					$controller->db->updateAsDict('game_users_info', ['fleet_shortcut' => $inf['fleet_shortcut']], 'id = '.$controller->user->getId());

					if (isset($_SESSION['fleet_shortcut']))
						unset($_SESSION['fleet_shortcut']);

					$controller->message("Ссылка была успешно удалена!", "Удаление ссылки", "?set=fleet&page=shortcut");
				}
				else
				{
					$r = explode(",", $scarray[$a]);

					$_POST['n'] = str_replace(',', '', $_POST['n']);

					$r[0] = strip_tags($_POST['n']);
					$r[1] = intval($_POST['g']);
					$r[2] = intval($_POST['s']);
					$r[3] = intval($_POST['p']);
					$r[4] = intval($_POST['t']);

					if ($r[1] < 1 || $r[1] > $controller->config->game->maxGalaxyInWorld)
						$r[1] = 1;
					if ($r[2] < 1 || $r[2] > $controller->config->game->maxSystemInGalaxy)
						$r[2] = 1;
					if ($r[3] < 1 || $r[3] > $controller->config->game->maxPlanetInSystem)
						$r[3] = 1;
					if ($r[4] != 1 && $r[4] != 2 && $r[4] != 3 && $r[4] != 5)
						$r[4] = 1;

					$scarray[$a] = implode(",", $r);
					$inf['fleet_shortcut'] = implode("\r\n", $scarray);

					$controller->db->updateAsDict('game_users_info', ['fleet_shortcut' => $inf['fleet_shortcut']], 'id = '.$controller->user->getId());

					if (isset($_SESSION['fleet_shortcut']))
						unset($_SESSION['fleet_shortcut']);

					$controller->message("Ссылка была обновлена!", "Обновление ссылки", "?set=fleet&page=shortcut");
				}
			}

			if ($inf['fleet_shortcut'])
			{
				$a = $controller->request->getPost('a', 'int', 0);
				$scarray = explode("\r\n", $inf['fleet_shortcut']);

				if (isset($scarray[$a]))
				{
					$c = explode(',', $scarray[$a]);

					$controller->view->pick('fleet/shortcut_edit');
					$controller->view->setVar('c', $c);
					$controller->view->setVar('a', $a);
				}
				else
					$controller->message("Данной ссылки не существует!", "Ссылки", "?set=fleet&page=shortcut");
			}
			else
				$controller->message("Ваш список быстрых ссылок пуст!", "Ссылки", "?set=fleet&page=shortcut");
		}
		else
		{

			$html = '<table class="table"><tr height="20"><td colspan="2" class="c">Ссылки (<a href="?set=fleet&page=shortcut&mode=add">Добавить</a>)</td></tr>';

			if ($inf['fleet_shortcut'])
			{

				$scarray = explode("\r\n", $inf['fleet_shortcut']);
				$i = $e = 0;
				foreach ($scarray as $a => $b)
				{
					if ($b != "")
					{
						$c = explode(',', $b);

						if ($i == 0)
							$html .= "<tr height=\"20\">";

						$html .= "<th width=50%><a href=\"?set=fleet&page=shortcut&a=" . $e++ . "\">";
						$html .= "{$c[0]} {$c[1]}:{$c[2]}:{$c[3]}";

						if ($c[4] == 2)
							$html .= " (E)";
						elseif ($c[4] == 3)
							$html .= " (L)";
						elseif ($c[4] == 5)
							$html .= " (B)";

						$html .= "</a></th>";

						if ($i == 1)
							$html .= "</tr>";

						if ($i == 1)
							$i = 0;
						else
							$i = 1;
					}


				}
				if ($i == 1)
					$html .= "<th>&nbsp;</th></tr>";

			}
			else
				$html .= "<th colspan=\"2\">Список ссылок пуст</th>";

			$html .= '<tr><td colspan=2 class=c><a href=?set=fleet>Назад</a></td></tr></tr></table>';
		}

		$controller->tag->setTitle("Закладки");
		$controller->view->setVar('html', $html);
	}
}

?>