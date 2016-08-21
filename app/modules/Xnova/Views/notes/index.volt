<form action="{{ url('notes/') }}" method="post">
	<table class="table">
		<tr>
			<td class="c" colspan="4">{{ _text('xnova', 'Notes') }}</td>
		</tr>
		<tr>
			<th colspan="4"><a href="{{ url('notes/new/') }}">{{ _text('xnova', 'MakeNewNote') }}</a></th>
		</tr>
		<tr>
			<td class="c"></td>
			<td class="c">{{ _text('xnova', 'Date') }}</td>
			<td class="c">{{ _text('xnova', 'Subject') }}</td>
			<td class="c">{{ _text('xnova', 'Size') }}</td>
		</tr>
		{% if parse['list'] is defined and parse['list']|length > 0 %}
			{% for list in parse['list'] %}
				<tr>
					<th width="20"><input name="delmes{{ list['id'] }}" value="y" type="checkbox" title=""></th>
					<th width="150">{{ list['time'] }}</th>
					<th>
						<a href="{{ url('notes/edit/'~list['id']~'/') }}">
							<span style="color:{{ list['color'] }}">{{ list['title'] }}</span>
						</a>
					</th>
					<th align="right" width="40">{{ list['text'] }}</th>
				</tr>
			{% endfor %}
		{% else %}
			<tr>
				<th colspan="4">Заметки отсутствуют</th>
			</tr>
		{% endif %}
		<tr>
			<td colspan="4" align="right"><input value="{{ _text('xnova', 'Delete') }}" type="submit"></td>
		</tr>
	</table>
</form>