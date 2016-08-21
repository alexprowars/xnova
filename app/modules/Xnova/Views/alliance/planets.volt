<table class="table">
	<tr>
		<td class="c" colspan="2">Ваши военные базы</td>
	</tr>
	{% for p in parse['list'] %}
		<tr>
			<th>{{ p['name'] }} {{ planetLink(p) }} </th>
			<th width="200">
				{% if p['id_ally'] == 0 %}
					<input type="button" value="Сделать альянсовой" onclick="window.confirm('Вы действительно хотите сделать эту планету альянсовой? Данное действие необратимо!') ? window.location.href='{{ url('alliance/admin/edit/planets/ally/'~p['id']~'') }}' : false">
				{% else %}
					преобразована
				{% endif %}
			</th>
		</tr>
	{% endfor %}
	<tr>
		<td class="c" colspan="2">Информация</td>
	</tr>
	<tr>
		<th colspan="2">
			Доступно кредитов: <span class="{{ parse['need'] > parse['credits'] ? 'negative' : 'positive' }}">{{ parse['credits'] }}</span>
			<br>Необходимо кредитов: {{ parse['need'] }}
			<br><br>
			<ul>
				<li>Военную базу нельзя вернуть назад</li>
				<li>На военной базе отсутствуют врата</li>
			</ul>
		</th>
	</tr>
	<tr>
		<td class="c" colspan="2"><a href="{{ url('alliance/admin/edit/ally/') }}">вернутся к обзору</a></td>
	</tr>
</table>

