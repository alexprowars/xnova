<table class="table">
	<tr>
		<td class="c" colspan="6">Чёрный список</td>
	</tr>
	{% if bannedList|length %}
		<tr>
			<th width="110">Логин</th>
			<th width="130">Дата блокировки</th>
			<th width="130">Конец блокировки</th>
			<th width="306">Причина блокировки</th>
			<th width="100">Модератор</th>
		</tr>

		{% for u in bannedList %}
			<tr>
				<td class="b text-center"><router-link to="{{ url('players/'~u['who']~'/') }}">{{ u['user_1'] }}</router-link></td>
				<td class="b text-center">
					<small>{{ game.datezone("d/m/Y H:m:s", u['time']) }}</small>
				</td>
				<td class="b text-center">
					<small>{{ game.datezone("d/m/Y H:m:s", u['longer']) }}</small>
				</td>
				<td class="b text-center">{{ u['theme'] }}</td>
				<td class="b text-center"><router-link to="{{ url('players/'~u['author']~'/') }}">{{ u['user_2'] }}</router-link></td>
			</tr>
		{% endfor %}

		<tr>
			<td class="b text-center" colspan="5">Всего {{ bannedList|length }} аккаунтов заблокировано</td>
		</tr>

	{% else %}
		<tr>
			<th class="b text-center" colspan="5">Нет заблокированных игроков</th>
		</tr>
	{% endif %}
</table>