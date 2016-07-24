<form action="{{ url('alliance/admin/edit/'.$parse['form'].'/') }}" method="POST">
	<table class="table">
		<tr>
			<td class="c"><?=$parse['question'] ?></td>
		</tr>
		<tr>
			<th><input type="text" name="<?=$parse['name'] ?>" title=""> <input type="submit" value="Изменить" title=""></th>
		</tr>
		<tr>
			<td class="c"><a href="{{ url('alliance/admin/edit/ally/') }}">вернутся к обзору</a></td>
		</tr>
	</table>
</form>