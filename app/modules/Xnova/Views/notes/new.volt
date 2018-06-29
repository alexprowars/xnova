<router-form action="{{ url('notes/new/') }}">
	<table class="table">
		<tr>
			<td class="c" colspan="2">{{ _text('xnova', 'Createnote') }}</td>
		</tr>
		<tr>
			<th>Приоритет:
				<select name="u" title="">
					<option value="2" selected>{{ _text('xnova', 'Important') }}</option>
					<option value="1">{{ _text('xnova', 'Normal') }}</option>
					<option value="0">{{ _text('xnova', 'Unimportant') }}</option>
				</select>
			</th>
			<th>Тема:
				<input type="text" name="title" size="30" maxlength="30" value="" placeholder="Введите тему">
			</th>
		</tr>
		<tr>
			<th colspan="2" class="p-a-0">
				<text-editor></text-editor>
			</th>
		</tr>
		<tr>
			<td class="c" colspan="2">
				<input type="reset" value="{{ _text('xnova', 'Reset') }}">
				<input type="submit" value="{{ _text('xnova', 'Save') }}">
			</td>
		</tr>
	</table>
</router-form>
<span style="float:left;margin-left: 10px;margin-top: 10px;"><router-link to="{{ url('notes/') }}">Назад</router-link></span>