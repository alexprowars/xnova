<form method="POST" action="{{ url('fleet/shortcut/view/'~a~'/') }}">
	<table class="table">
		<tr>
			<td colspan="2" class="c">{{ c[0] }} [{{ c[1] }}:{{ c[2] }}:{{ c[3] }}]</td>
		</tr>
		<tr>
			<th>
				<input type="text" name="n" value="{{ c[0] }}" size=32 maxlength=32 title="Название">
				<input type="text" name="g" value="{{ c[1] }}" size=3 maxlength=2 title="Галактика">
				<input type="text" name="s" value="{{ c[2] }}" size=3 maxlength=3 title="Система">
				<input type="text" name="p" value="{{ c[3] }}" size=3 maxlength=2 title="Планета">
				<select name="t" title="">
					{% for key, value in _text('fleet_objects') %}
						<option value="{{ key }}" {{ c[4] == key ? 'selected' : '' }}>{{ value }}</option>
					{% endfor %}
				</select></th>
		</tr>
		<tr>
			<th>
				<input type="reset" value="Очистить"> <input type="submit" value="Обновить"> <input type="submit" name="delete" value="Удалить">
			</th>
		</tr>
		<tr>
			<td colspan="2" class="c"><a href="{{ url('fleet/shortcut/') }}">Назад</a></td>
		</tr>
	</table>
</form>