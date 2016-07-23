<?php
namespace Xnova\Admin\Controllers;

use App\Helpers;

class ActivePlanetController extends Application
{
	public function indexAction ()
	{
		if ($this->user->authlevel >= 2)
		{
			$result = [];
			$result['rows'] = [];

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

			$AllActivPlanet = $this->db->query("SELECT `name`, `galaxy`, `system`, `planet`, `last_active` FROM game_planets WHERE `last_update` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $s . "` ".$d." LIMIT ".$start.",".$limit."");

			while ($ActivPlanet = $AllActivPlanet->fetch())
			{
				$result['rows'][] = [
					'name' 		=> $ActivPlanet['name'],
					'position' 	=> Helpers::BuildPlanetAdressLink($ActivPlanet),
					'activity' 	=> (time() - $ActivPlanet['last_active'])
				];
			}

			$result['total'] = $this->db->fetchColumn("SELECT COUNT(id) AS num FROM game_planets WHERE `last_active` >= '" . (time() - 15 * 60) . "'");

			$this->view->pick('admin/activeplanet');
			$this->view->setVar('parse', $result);

			$pagination = Helpers::pagination($result['total'], $limit, '?set=admin&mode=activeplanet', $start);

			$this->view->setVar('pagination', $pagination);
			$this->tag->setTitle(_getText('adm_pl_title'));
		}
		else
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>