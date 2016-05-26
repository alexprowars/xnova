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

		if (!$this->request->has('InvId') || $_REQUEST["InvId"] == '' || !is_numeric($_REQUEST["InvId"]))
			die('InvId nulled');

		$sign_hash = strtoupper(md5("".$_REQUEST['OutSum'].":".$_REQUEST['InvId'].":".$this->config->robokassa->secret.":Shp_UID=".$_REQUEST['Shp_UID'].""));

		if (strtoupper($_REQUEST["SignatureValue"]) === $sign_hash)
		{
			$check = $this->db->fetchOne("SELECT id FROM game_users_payments WHERE transaction_id = '".intval($_REQUEST["InvId"])."' AND user != 0");

			if (!isset($check['id']))
			{
				$user = $this->db->fetchOne("SELECT id FROM game_users WHERE id = ".intval($_REQUEST["Shp_UID"])." LIMIT 1");

				if (isset($user['id']))
				{
					$amount = intval($_REQUEST['OutSum']);

					if ($amount > 0)
					{
						$this->db->query("UPDATE game_users SET credits = credits + ".$amount." WHERE id = ".$user['id']."");
						$this->db->query("INSERT INTO game_users_payments (user, call_id, method, transaction_id, transaction_time, uid, amount, product_code) VALUES (".$user['id'].", '', '".addslashes($_REQUEST['IncCurrLabel'])."', '".intval($_REQUEST["InvId"])."', '".date("Y-m-d H:i:s", time())."', '0', ".$amount.", '".addslashes(json_encode($_REQUEST))."')");

						User::sendMessage($user['id'], 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено '.$amount.' кредитов');

						$this->db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $user['id'] . ", " . time() . ", " . $amount . ", 1)");

						echo 'OK'.$_REQUEST["InvId"];
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