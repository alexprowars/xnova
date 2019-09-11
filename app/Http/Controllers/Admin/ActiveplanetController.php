<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Helpers;

class ActiveplanetController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'activeplanet',
			'title' => 'Активные планеты',
			'icon'	=> 'globe',
			'sort'	=> 90
		]];
	}

	public function index ()
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

		$AllActivPlanet = $this->db->query("SELECT `name`, `galaxy`, `system`, `planet`, `last_active` FROM planets WHERE `last_update` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $s . "` ".$d." LIMIT ".$start.",".$limit."");

		while ($ActivPlanet = $AllActivPlanet->fetch())
		{
			$result['rows'][] = [
				'name' 		=> $ActivPlanet['name'],
				'position' 	=> Helpers::BuildPlanetAdressLink($ActivPlanet),
				'activity' 	=> (time() - $ActivPlanet['last_active'])
			];
		}

		$result['total'] = $this->db->fetchColumn("SELECT COUNT(id) AS num FROM planets WHERE `last_active` >= '" . (time() - 15 * 60) . "'");

		$pagination = Helpers::pagination($result['total'], $limit, '?set=admin&mode=activeplanet', $start);

		View::share('title', __('adm_pl_title'));

		return view('admin.activeplanet', ['parse' => $result, 'pagination' => $pagination]);
	}
}