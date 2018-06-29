<router-form action="{{ url('notes/edit/'~parse['id']~'/') }}">
	<table class="table">
		<tr>
			<td class="c">Просмотр заметки</td>
		</tr>
		<tr>
			<th style="text-align:left;font-weight:normal;">
				<text-viewer text="{{ parse['text'] }}"></text-viewer>
			</th>
		</tr>
	</table>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="2">{{ _text('xnova', 'Editnote') }}</td>
		</tr>
		<tr>
			<th>Приоритет:
				<select name="u" title="">
					<option value="2" {{ parse['priority'] == 2 ? 'selected' : '' }}>{{ _text('xnova', 'Important') }}</option>
					<option value="1" {{ parse['priority'] == 1 ? 'selected' : '' }}>{{ _text('xnova', 'Normal') }}</option>
					<option value="0" {{ parse['priority'] == 0 ? 'selected' : '' }}>{{ _text('xnova', 'Unimportant') }}</option>
				</select>
			</th>
			<th>Тема:
				<input type="text" name="title" size="30" maxlength="30" value="{{ parse['title'] }}" placeholder="Введите тему">
			</th>
		</tr>
		<tr>
			<th colspan="2" class="p-a-0">
				<text-editor text="{{ parse['text'] }}"></text-editor>
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