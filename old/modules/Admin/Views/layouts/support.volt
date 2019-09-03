<div class="card">
	<table class="table table-striped table-hover table-sm table-responsive">
		<thead>
			<tr>
				<th width="50">ID</th>
				<th width="20%">Игрок</th>
				<th>Тема</th>
				<th width="150">Статус</th>
				<th width="150">Дата</th>
			</tr>
		</thead>
		{% for list in tickets['open'] %}
		<tr>
			<td>{{ list['id'] }}</td>
			<td>{{ list['username'] }}</td>
			<td><a href="{{ url('support/detail/'~list['id']~'/') }}">{{ list['subject'] }}</a></td>
			<td>{{ list['status'] }}</td>
			<td>{{ list['date'] }}</td>
		</tr>
		{% endfor %}
		{% if tickets['open']|length == 0 %}
			<th colspan="5" class="c">Нет новых запросов</th>
		{% endif %}
	</table>
</div>

{% if tickets['closed']|length > 0 %}
	<div class="card">
		<table class="table">
			<tr>
				<td colspan="5" class="c text-xs-center">
					Служба техподдержки
				</td>
			</tr>
			<tr>
				<td class="c text-xs-center" width="10%">
					ID
				</td>
				<td class="c text-xs-center" width="10%">
					Игрок
				</td>
				<td class="c text-xs-center" width="40%">
					Тема
				</td>
				<td class="c text-xs-center" width="15%">
					Статус
				</td>
				<td class="c text-xs-center" width="25%">
					Дата
				</td>
			</tr>
			{% for list in tickets['closed'] %}
			<tr>
				<th>{{ list['id'] }}</th>
				<th>{{ list['username'] }}</th>
				<th><a href="{{ url('support/detail/'~list['id']~'/') }}">{{ list['subject'] }}</a></th>
				<th>{{ list['status'] }}</th>
				<th>{{ list['date'] }}</th>
			</tr>
			{% endfor %}
		</table>
	</div>
{% endif %}

{% if parse['t_id'] is defined %}
	<div class="card">
		<div class="card-title">Тикет №{{ parse['t_id'] }}</div>
		<div class="card-body">
			<table class="table table-sm">
				<thead>
					<tr>
						<th width="20%">
							Игрок
						</th>
						<th>
							Тема
						</th>
						<th width="150">
							Статус
						</th>
						<th width="150">
							Дата
						</th>
					</tr>
				</thead>
				<tr>
					<td>{{ parse['t_username'] }}</td>
					<td>{{ parse['t_subject'] }}</a></td>
					<td>{{ parse['t_statustext'] }}</td>
					<td>{{ parse['t_date'] }}</td>
				</tr>
			</table>

			<form action="{{ url('support/send/'~parse['t_id']~'/') }}" method="POST">
				<div class="form-group">
					<label>Текст запроса</label>
					<div class="form-control-plaintext">{{ parse['t_text'] }}</div>
				</div>
				<div class="form-group">
					<label>Ответ</label>
					<textarea class="form-control" rows="10" name="text" title=""></textarea>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Ответить</button>
				</div>
			</form>

			<hr>
			{% if parse['t_status'] != 0 %}
				<form action="{{ url('support/close/'~parse['t_id']~'/') }}" method="POST">
					<input class="btn btn-danger" type="submit" value="Закрыть">
				</form>
			{% else %}
				<form action="{{ url('support/open/'~parse['t_id']~'/') }}" method="POST">
					<input class="btn btn-primary" type="submit" value="Открыть">
				</form>
			{% endif %}
		</div>
	</div>
{% endif %}