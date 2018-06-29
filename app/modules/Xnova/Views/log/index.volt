<table class="table">
	<tr>
		<th colspan="4">Логовница</th>
	</tr>
	<tr>
		<td class="c" colspan="4">Ваши сохранённые логи</td>
	</tr>
	<tr>
		<td class="c">№</td>
		<td class="c">Название</td>
		<td class="c">Ссылка</td>
		<td class="c">Управление логом</td>
	</tr>
	{% for i, row in list %}
		<tr>
			<td class="b text-center">{{ i + 1 }}</td>
			<td class="b text-center">{{ row['title'] }}</td>
			<td class="b text-center">
				<a href="{{ url('log/'~row['id']~'/') }}" {{ config.game.get('openRaportInNewWindow', 0) == 1 ? 'target="_blank"' : '' }}>Открыть</a>
			</td>
			<td class="b text-center"><router-link to="{{ url('log/delete/id/'~row['id']~'/') }}">Удалить лог</router-link></td>
		</tr>
	{% endfor %}
	{% if list|length == 0 %}
		<tr align="center">
			<td class="b text-center" colspan="4">У вас пока нет сохранённых логов.</td>
		</tr>
	{% endif %}
	<tr>
		<td class="c" colspan="4"><router-link to="{{ url('log/new/') }}">Создать новый лог боя</router-link></td>
	</tr>
</table>