<form action="{{ url('notes/edit/'~parse['id']~'/') }}" method="post">
	<table class="table">
		<tr>
			<td class="c">Просмотр заметки</td>
		</tr>
		<tr>
			<th style="text-align:left;font-weight:normal;">
				<span id="um{{ parse['id'] }}" style="display:none;"></span>
				<span id="m{{ parse['id'] }}"></span>
				<script>Text('{{ replace(["\n", "\r", "\n\r"], '<br>', parse['text']|slashes) }}', 'm{{ parse['id'] }}');</script>
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
					<option value="0" {{ parse['priority'] == 0 ? 'selected' : '' }}>{{ _text('xnova', 'Unimportant') }}</option
				</select>
			</th>
			<th>Тема:
				<input type="text" name="title" size="30" maxlength="30" value="{{ parse['title'] }}" placeholder="Введите тему">
			</th>
		</tr>
		<tr>
			<th colspan="2" class="p-a-0">
				<div id="editor"></div>
				<textarea name="text" id="text" rows="10" placeholder="Введите текст">{{ parse['text'] }}</textarea>
				<script type="text/javascript">edToolbar('text');</script>
			</th>
		</tr>
		<tr>
			<td class="c" colspan="2">
				<input type="reset" value="{{ _text('xnova', 'Reset') }}">
				<input type="submit" value="{{ _text('xnova', 'Save') }}">
			</td>
		</tr>
	</table>
	<div id="showpanel" style="display:none">
		<table align="center" width='651'>
			<tr>
				<td class="c"><b>Предварительный просмотр</b></td>
			</tr>
			<tr>
				<td class="b"><span id="showbox"></span></td>
			</tr>
		</table>
	</div>
</form>
<span style="float:left;margin-left: 10px;margin-top: 10px;"><a href="{{ url('notes/') }}">Назад</a></span>