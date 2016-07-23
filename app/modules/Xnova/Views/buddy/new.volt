<?
/**
 * @var $parse array
 */
?>
<form action="<?=$this->url->get('buddy/new/'.$parse["id"].'/') ?>" method="post">
	<table class="table">
		<tr>
			<td class="c" colspan="2">Предложение подружиться</td>
		</tr>
		<tr>
			<th>Игрок</th>
			<th><?=$parse["username"] ?></th>
		</tr>
		<tr>
			<th colspan="2"><textarea name="text" cols="60" rows="10" title=""></textarea></th>
		</tr>
		<tr>
			<td class="c"><a href="javascript:back();">назад</a></td>
			<td class="c"><input type="submit" value="Отправить заявку"></td>
		</tr>
	</table>
</form>