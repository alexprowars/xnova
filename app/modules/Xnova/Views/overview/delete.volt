<form action="<?=$this->url->get("overview/delete/") ?>" method=POST>
	<input type="hidden" name="password" value="<?=md5($parse['number_check']) ?>">
	<table class="table">
		<tr>
			<td class="c" colspan="3">Система безопасности</td>
		</tr>
		<tr>
			<th colspan="3">Подтвердите удаление планеты <?=$parse['galaxy'] ?>:<?=$parse['system'] ?>:<?=$parse['planet'] ?> вводом правильного ответа</th>
		</tr>
		<tr>
			<th><?=$parse['number_1'] ?> + <?=$parse['number_2'] ?> * <?=$parse['number_3'] ?> = ???</th>
			<th><input type="text" name="pw" title=""></th>
			<th><input type="submit" name="action" value="Удалить колонию"></th>
		</tr>
	</table>
	<input type="hidden" name="id" value="<?=$parse['id'] ?>">
</form>