<form method="POST" action="{{ url('fleet/shortcut/add/new/') }}">
	<table class="table">
		<tr>
			<td colspan="2" class="c">Имя [Галактика:Система:Планета]</td>
		</tr>
		<tr>
			<th>
				<input type="text" name="n" value="" size=32 maxlength=32 title="Название">
				<input type="text" name="g" value="{{ g }}" size=3 maxlength=2 title="Галактика">
				<input type="text" name="s" value="{{ s }}" size=3 maxlength=3 title="Система">
				<input type="text" name="p" value="{{ i }}" size=3 maxlength=2 title="Планета">
				<select name="t" title="">
					{% for key, value in _text('xnova', 'fleet_objects') %}
						<option value="{{ key }}" {{ t == key ? 'selected' : '' }}>{{ value }}</option>
					{% endfor %}
				</select></th>
		</tr>
		<tr>
			<th>
				<input type="reset" value="Очистить"> <input type="submit" value="Добавить">
			</th>
		</tr>
		<tr>
			<td colspan="2" class="c"><a href="{{ url('fleet/shortcut/') }}">Назад</a></td>
		</tr>
	</table>
</form>