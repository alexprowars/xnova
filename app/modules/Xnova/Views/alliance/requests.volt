<table class="table">
	<tr>
		<td class="c" colspan="2">Обзор заявок [{{ parse['tag'] }}]</td>
	</tr>
	{% if parse['request'] is type('array') %}
		<tr><td colspan="2">
		<form action="{{ url('alliance/admin/edit/requests/show/'~parse['request']['id']~'/sort/0/') }}" method="POST">
			<table width="100%">
			<tr>
				<th colspan="2">Заявка от {{ parse['request']['username'] }}</th>
			</tr>
			<tr>
				<th colspan="2">{{ parse['request']['request_text'] }}</th>
			</tr>
			<tr>
				<td class="c" colspan="2">Форма ответа:</td>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" name="action" value="Принять"></th>
			</tr>
			<tr>
				<th colspan="2"><textarea name="text" cols=40 rows=10 title=""></textarea></th>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" name="action" value="Отклонить"></th>
			</tr>
			</table>
		</form>
		</td></tr>
	{% endif %}
	{% if parse['list']|length > 0 %}
		<tr>
			<td class="c text-center">
				<a href="{{ url('alliance/admin/edit/requests/show/0/sort/1/') }}">Логин</a>
			</td>
			<td class="c text-center">
				<a href="{{ url('alliance/admin/edit/requests/show/0/sort/0/') }}">Дата подачи заявки</a>
			</td>
		</tr>
		{% for list in parse['list'] %}
			<tr>
				<th class="text-center">
					<a href="{{ url('alliance/admin/edit/requests/show/'~list['id']~'/sort/0/') }}">{{ list['username'] }}</a>
				</th>
				<th class="text-center">
					{{ list['time'] }}
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr>
			<th colspan="2">Список заявок пуст</th>
		</tr>
	{% endif %}
	<tr>
		<td class="c" colspan="2"><a href="{{ url('alliance/') }}">Назад</a></td>
	</tr>
</table>