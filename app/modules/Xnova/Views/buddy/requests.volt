<table class="table">
	<tr>
		<td class="c" colspan="6">{{ parse['title'] }}</td>
	</tr>
	<tr>
		<td class="c">&nbsp;</td>
		<td class="c">Пользователь</td>
		<td class="c">Альянс</td>
		<td class="c">Координаты</td>
		<td class="c">Текст</td>
		<td class="c">&nbsp;</td>
	</tr>
	{% if parse['list']|length %}
		{% for id, list in parse['list'] %}
			<tr>
				<th width="20">{{ id + 1 }}</th>
				<th><router-link to="{{ url('messages/write/'~list['userid']~'') }}/">{{ list['username'] }}</router-link></th>
				<th>{{ list['ally'] }}</th>
				<th><router-link to="{{ url('galaxy/'~list['g']~'/'~list['s']~'/') }}">{{ list['g'] }}:{{ list['s'] }}:{{ list['p'] }}</router-link></th>
				<th>{{ list['online'] }}</th>
				<th>
					{% if parse['isMy'] %}
						<router-link to="{{ url('buddy/delete/'~list['id']~'/') }}">Удалить запрос</router-link>
					{% else %}
						<router-link to="{{ url('buddy/approve/'~list['id']~'/') }}">Применить</router-link>
						<br/>
						<router-link to="{{ url('buddy/delete/'~list['id']~'/') }}">Отклонить</router-link>
					{% endif %}
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr>
			<th colspan="6">Нет друзей</th>
		</tr>
	{% endif %}
	<tr>
		<td colspan="6" class="c"><router-link to="{{ url('buddy/') }}">назад</router-link></td>
	</tr>
</table>
