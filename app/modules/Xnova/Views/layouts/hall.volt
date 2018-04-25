<table class="table">
	<tr>
		<td class="c" width="100">TOP50</td>
		<td class="c"><a href="{{ url('hall/') }}">Зал Славы</a></td>
		<td class="c" width="137">
			<form method="post" action="{{ url('hall/') }}" id="hall">
				<select name="visible" onChange="$('#hall').submit()" title="">
					<option value="1" {{ request.getPost('visible', 'int', 0) <= 1 ? 'selected' : '' }}>Бои</option>
					<option value="2" {{ request.getPost('visible', 'int', 0) == 2 ? 'selected' : '' }}>САБ</option>
				</select>
			</form>
		</td>
	</tr>
</table>
<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" width="35">Место</td>
			<td class="c"><font color=#CDB5CD>{{ request.getPost('visible', 'int', 0) <= 1 ? 'Самые разрушительные бои' : 'Самые разрушительные групповые бои' }}</font></td>
			<td class="c" width="45">Итог</td>
			<td class="c" width="125">Дата</td>
		</tr>
		{% if parse['hall']|length > 0 %}
		{% set i = 0 %}
		{% for log in parse['hall'] %}
			{% set i = i + 1 %}
			<tr>
				<th>{{ i }}</th>
				<th><a href="{{ url('log/'~log['log']~'/') }}" {{ config.view.get('openRaportInNewWindow', 0) ? 'target="_blank"' : '' }}>{{ log['title'] }}</a></th>
				<th>
					{% if log['won'] == 0 %}
						Н
					{% elseif log['won'] == 1 %}
						А
					{% else %}
						О
					{% endif %}
				</th>
				<th nowrap>
					{% if parse['time'] == log['time'] %}
						<font color="green">
					{% endif %}
					{{ game.datezone("d.m.y H:i", log['time']) }}
					{% if parse['time'] == log['time'] %}
						</font>
					{% endif %}
				</th>
			</tr>
			{% endfor %}
		{% else %}
		<tr>
			<th colspan="4">В этой вселенной еще не было крупных боев</th>
		</tr>
		{% endif %}
	</table>