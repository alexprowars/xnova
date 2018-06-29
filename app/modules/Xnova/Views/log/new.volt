<table class="table">
	<tr>
		<td class="c"><h1>Сохранение боевого доклада</h1></td>
	</tr>
	<tr>
		<th>
			<router-form action="{{ url('log/new/') }}">
				Название:<br>
				<input type="text" name="title" size="50" maxlength="100" title=""><br>
				ID боевого доклада:<br>
				<input type="text" name="code" size="50" maxlength="40" value="{{ request.getQuery('code', 'string', '') }}" title="">
				<br>
				<br><input type="submit" value="Сохранить">
			</router-form>
		</th>
	</tr>
</table>