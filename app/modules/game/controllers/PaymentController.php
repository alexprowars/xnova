<?php
namespace App\Controllers;
use App\Models\User;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

class PaymentController extends Application
{
	public function indexAction ()
	{

	}

	public function robokassaAction ()
	{
		$this->view->disable();

		if (!$this->request->has('InvId') || $this->request->get("InvId") == '' || !is_numeric($this->request->get("InvId")))
			die('InvId nulled');

		$sign_hash = strtoupper(md5($this->request->get('OutSum').":".$this->request->get('InvId').":".$this->config->robokassa->secret.":Shp_UID=".$this->request->get('Shp_UID')));

		if (strtoupper($this->request->get('SignatureValue')) === $sign_hash)
		{
			$check = $this->db->fetchOne("SELECT id FROM game_users_payments WHERE transaction_id = '".intval($this->request->get("InvId"))."' AND user != 0");

			if (!isset($check['id']))
			{
				/**
				 * @var $user \App\Models\User
				 */
				$user = User::findFirst(['conditions' => 'id = ?0', 'bind' => [intval($this->request->get("Shp_UID"))]]);

				if ($user)
				{
					$amount = intval($_REQUEST['OutSum']);

					if ($amount > 0)
					{
						if (!$this->request->has('IncCurrLabel'))
							$_REQUEST['IncCurrLabel'] = 'Free-Kassa';

						$user->credits += $amount;
						$user->save();

						$this->db->insertAsDict('game_users_payments', [
							'user' 				=> $user->id,
							'call_id' 			=> '',
							'method' 			=> addslashes($_REQUEST['IncCurrLabel']),
							'transaction_id' 	=> intval($this->request->get("InvId")),
							'transaction_time' 	=> date("Y-m-d H:i:s", time()),
							'uid' 				=> 0,
							'amount' 			=> $amount,
							'product_code' 		=> addslashes(json_encode($_REQUEST)),
						]);

						User::sendMessage($user->id, 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено '.$amount.' кредитов');

						$this->db->insertAsDict('game_log_credits', [
							'uid' 		=> $user->id,
							'time' 		=> time(),
							'credits' 	=> $amount,
							'type' 		=> 1,
						]);

						echo 'OK'.$this->request->get("InvId");
					}
				}
				else
					echo 'userId not found';
			}
			else
				echo 'already paid';
		}
		else
			echo 'signature verification failed';
	}
}