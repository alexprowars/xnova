<?php

use Xnova\Models\User;
use Phalcon\Mvc\Url as UrlProvider;

define('ROOT_PATH', '/var/www/xnova/data/www/uni5.xnova.su/');

ini_set('log_errors', 'On');
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', ROOT_PATH.'php_errors.log');

try
{
	require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
	require_once(ROOT_PATH."/app/modules/Core/Classes/Application.php");

	$application = new Friday\Core\Application();
	$application->run();

	\Friday\Core\Modules::init('xnova');

	$params = $application->request->getQuery();
	ksort($params);
	unset($params['sig']);

	$s = '';

	foreach($params as $k => $v)
		$s .= $k.'='.$v;

	$signature = md5($s.$application->config->ok->private);

	if (strcmp($application->request->getQuery('sig'), $signature) == 0)
	{
		$extra = json_decode($params['extra_attributes'], true);

		$amount = intval($params['amount']);

		$check = $application->db->query("SELECT id FROM game_users_payments WHERE transaction_id = '" . $params['transaction_id'] . "' AND user != 0")->fetch();

		if (!isset($check['id']))
		{
			if (!isset($extra['userId']))
			{
				$error = 1001;
				$errorstr = "Payment is invalid and can not be processed";
				$result = "Error amount: {$amount} okid: {$_GET['uid']}";
			}
			else
			{
				$user = $application->db->query("SELECT id FROM game_users WHERE id = " . intval($extra['userId']) . "")->fetch();

				if (!isset($user['id']))
				{
					$error = 1001;
					$errorstr = "Payment is invalid and can not be processed";
					$result = "Not found user: {$_GET['amount']} {$_GET['uid']}";
				}
				else
				{
					if ($amount == 20 || $amount == 60 || $amount == 100 || $amount == 200 || $amount == 500)
						$amount += floor($amount * 0.1);

					if ($amount > 0)
					{
						$application->db->query("UPDATE game_users SET credits = credits + " . $amount . " WHERE id = " . $user['id'] . "");

						User::sendMessage($user['id'], 0, 0, 1, 'Обработка платежей', 'На ваш счет зачислено ' . $amount . ' кредитов');

						$application->db->query("INSERT INTO game_users_payments (user, call_id, method, transaction_id, transaction_time, uid, amount) VALUES (" . $user['id'] . ", '" . $_GET['call_id'] . "', '" . $_GET['method'] . "', '" . $_GET['transaction_id'] . "', '" . $_GET['transaction_time'] . "', '" . $_GET['uid'] . "', " . $amount . ")");

						$result = "Byed ok: {$amount}.";
						$error = 0;
					}
					else
					{
						$error = 1001;
						$errorstr = "Payment is invalid and can not be processed";
						$result = "Error amount: {$amount} okid: {$_GET['uid']}";
					}
				}
			}
		}
		else
		{
			$result = "Byed ok: {$amount}.";
			$error = 0;
		}
	}
	else
	{
		$error = 104;
		$errorstr = "Invalid signature";
		$result ="Invalid signature".$_GET['sig']." ".$signature;
	}

	$application->response->setContentType('application/xml', 'utf8');
	$application->response->send();

	if (!$error)
	{
		echo '<?xml version="1.0" encoding="UTF-8"?><callbacks_payment_response xmlns="http://api.forticom.com/1.0/">true</callbacks_payment_response>';
	}
	else
	{
		$application->db->query("INSERT INTO game_users_payments (user, call_id, method, transaction_id, transaction_time, uid, amount) VALUES (0, '".$_GET['call_id']."', '".$_GET['method']."', '".$_GET['transaction_id']."', '".$_GET['transaction_time']."', '".$_GET['uid']."', -1)");

		printMsg($error, $errorstr);
	}
}
catch(\Exception $e)
{
	echo "PhalconException: ", $e->getMessage();
	echo "<br>".$e->getFile();
	echo "<br>".$e->getLine();
}

function printMsg($error, $errorstr)
{
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><ns2:error_response xmlns:ns2=\"http://api.forticom.com/1.0/\"><error_code>{$error}</error_code><error_msg>{$errorstr}</error_msg></ns2:error_response>";
}

?>