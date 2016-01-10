<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;

class ActivePlanet
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 2)
		{
			$result = array();
			$result['rows'] = array();

			$start = isset($_GET['p']) ? intval($_GET['p']) : 0;
			$limit = 25;

			if (isset($_GET['sort']))
			{
				$sort 	= $_GET['sort'];
				$d 		= $_GET['dir'];

				switch ($sort['property'])
				{
					case 'name':
						$s = 'id';
					break;
					case 'position':
						$s = 'galaxy '.$d.', system '.$d.', planet';
					break;
					case 'activity':
						$s = 'last_active';
					break;
					default:
						$s = 'id';
				}
			}
			else
			{
				$s = 'name';
				$d = 'ASC';
			}

			$AllActivPlanet = $controller->db->query("SELECT `name`, `galaxy`, `system`, `planet`, `last_active` FROM game_planets WHERE `last_update` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $s . "` ".$d." LIMIT ".$start.",".$limit."");

			while ($ActivPlanet = $AllActivPlanet->fetch())
			{
				$result['rows'][] = array
				(
					'name' 		=> $ActivPlanet['name'],
					'position' 	=> Helpers::BuildPlanetAdressLink($ActivPlanet),
					'activity' 	=> (time() - $ActivPlanet['last_active'])
				);
			}

			$result['total'] = $controller->db->fetchColumn("SELECT COUNT(id) AS num FROM game_planets WHERE `last_active` >= '" . (time() - 15 * 60) . "'");

			$controller->view->pick('admin/activeplanet');
			$controller->view->setVar('parse', $result);

			$pagination = Helpers::pagination($result['total'], $limit, '?set=admin&mode=activeplanet', $start);

			$controller->view->setVar('pagination', $pagination);
			$controller->tag->setTitle(_getText('adm_pl_title'));
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>