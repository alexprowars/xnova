<table class="table">
	<tr>
		<td class="c" colspan="2">Обзор заявок [{{ parse['tag'] }}]</td>
	</tr>
	{% if parse['request'] is type('array') %}
		<tr>
			<td colspan="2" class="padding-0">
				<form action="{{ url('alliance/admin/edit/requests/show/'~parse['request']['id']~'/') }}" method="post">
					<div class="separator"></div>
					<div class="table">
						<div class="row">
							<div class="col th">Заявка от {{ parse['request']['username'] }}</div>
						</div>
						<div class="row">
							<div class="col th">{{ parse['request']['request_text'] }}</div>
						</div>
						<div class="row">
							<div class="col c">Форма ответа:</div>
						</div>
						<div class="row">
							<div class="col th"><input type="submit" name="action" value="Принять"></div>
						</div>
						<div class="row">
							<div class="col th"><textarea name="text" cols=40 rows=10 title=""></textarea></div>
						</div>
						<div class="row">
							<div class="col th"><input type="submit" name="action" value="Отклонить"></div>
						</div>
					</div>
					<div class="separator"></div>
				</form>
			</td>
		</tr>
	{% endif %}
	{% if parse['list']|length > 0 %}
		<tr>
			<td class="c text-center">
				<a href="{{ url('alliance/admin/edit/requests/sort/1/') }}">Логин</a>
			</td>
			<td class="c text-center">
				<a href="{{ url('alliance/admin/edit/requests/sort/0/') }}">Дата подачи заявки</a>
			</td>
		</tr>
		{% for list in parse['list'] %}
			<tr>
				<th class="text-center">
					<a href="{{ url('alliance/admin/edit/requests/show/'~list['id']~'/') }}">{{ list['username'] }}</a>
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