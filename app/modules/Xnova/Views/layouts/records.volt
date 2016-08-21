<table class="table">
	<tr>
		<th colspan="3" style="text-align:center;">Обновлено в {{ game.datezone("H:i:s d.m.Y", parse['update']) }}</th>
	</tr>
	{% for group, list in parse['Records'] %}
		<tr>
			<td class="c" style="width:199px">{{ group }}</td>
			<td class="c" style="width:203px">Игрок</td>
			<td class="c" style="width:172px">Уровень</td>
		</tr>
		{% for building, info in list %}
		<tr>
			<th style="width:309px">{{ building }}</th>
			<th style="width:203px">{{ info['winner'] }}</th>
			<th style="width:82px">{{ info['count'] }}</th>
		</tr>
		{% endfor %}
	{% endfor %}
</table>