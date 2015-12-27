<?php

namespace App\Controllers;

class PayController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function show2 ()
	{
		$summa = intval($_POST['rur']);

		if ($summa < 15)
			$this->message('Минимальный платёж 15 рублей', 'Ошибка оплаты', '?set=infokredits', 3);

		$this->db->query("INSERT INTO game_wmrlog (pay_id, user_id, username, date_start) VALUES (0, " . $this->user->getId() . ", '" . $this->user->username . "', " . time() . ")");

		$id = $this->db->lastInsertId();

		$page = "<br><br><form method=\"POST\" action=\"https://merchant.webmoney.ru/lmi/payment.asp\" target=\"_blank\">";
		$page .= "<table>";
		$page .= "<tr><td class=\"c\" colspan=2>Покупка игровых кредитов</td></tr>";
		$page .= "<tr><th>Сумма покупки (руб):</th><th><input type=\"hidden\" name=\"LMI_PAYMENT_AMOUNT\" value=\"" . $summa . "\"><b>" . $summa . " р. (" . $summa . " кр.)</b> (1 рубль = 1 кредит)</th>";
		$page .= "<tr><th>Примечание к платежу</th><th><input type=\"hidden\" name=\"LMI_PAYMENT_DESC\" value=\"Покупка XNOVA кредитов (номер: " . $id . ", пользователь: " . $this->user->username . ")\">Покупка XNOVA кредитов</th></tr>";
		$page .= "<input type=\"hidden\" name=\"USERNAME\" value = \"" . $this->user->username . "\">";
		$page .= "<input type=\"hidden\" name=\"LMI_PAYEE_PURSE\" value=\"R356399779340\">";
		$page .= "<input type=\"hidden\" name=\"LMI_PAYMENT_NO\" value=\"" . $id . "\">";
		$page .= "<tr><td class=\"c\" colspan=2><input type=\"submit\" value=\"Оплатить\"></td></tr>";
		$page .= "</form></table>";


		$this->display($page, "Оплата кредитов", false);
	}
}

?>