<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Friday\Core\Lang;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\Fleet;

class Back
{
	public function show (FleetController $controller)
	{
		$fleetId = (int) $controller->request->getPost('id', 'int', 0);

		if ($fleetId <= 0)
			throw new ErrorException('Не выбран флот');

		Lang::includeLang('fleet', 'xnova');

		$fleet = Fleet::findFirst($fleetId);

		if (!$fleet || $fleet->owner != $controller->user->id)
			throw new ErrorException(_getText('fl_onlyyours'));

		if (!$fleet->canBack())
			throw new ErrorException(_getText('fl_notback'));

		if ($fleet->end_stay != 0)
		{
			if ($fleet->start_time > time())
				$CurrentFlyingTime = time() - $fleet->create_time;
			else
				$CurrentFlyingTime = $fleet->start_time - $fleet->create_time;
		}
		else
			$CurrentFlyingTime = time() - $fleet->create_time;

		$ReturnFlyingTime = $CurrentFlyingTime + time();

		if ($fleet->group_id != 0 && $fleet->mission == 1)
		{
			$controller->db->delete('game_aks', 'id = ?', [$fleet->group_id]);
			$controller->db->delete('game_aks_user', 'aks_id = ?', [$fleet->group_id]);
		}

		$fleet->update([
			'start_time'	=> time() - 1,
			'end_stay' 		=> 0,
			'end_time' 		=> $ReturnFlyingTime + 1,
			'target_owner' 	=> $controller->user->id,
			'group_id' 		=> 0,
			'update_time' 	=> $ReturnFlyingTime + 1,
			'mess' 			=> 1,
		]);

		throw new RedirectException(_getText('fl_isback'), '/xnova/');
	}
}