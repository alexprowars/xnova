<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
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
			<td><a href="/admin/support/detail/{{ list['id'] }}/">{{ list['subject'] }}</a></td>
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
	<br><br>
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
			<th><a href="/admin/support/detail/{{ list['id'] }}/">{{ list['subject'] }}</a></th>
			<th>{{ list['status'] }}</th>
			<th>{{ list['date'] }}</th>
		</tr>
		{% endfor %}
	</table>
{% endif %}
{% if parse['t_id'] is defined %}
	<br><br>
	<table class="table table-advance">
		<thead>
			<tr>
				<th width="50">
					<center>ID</center>
				</th>
				<th width="20%">
					<center>Игрок</center>
				</th>
				<th>
					<center>Тема</center>
				</th>
				<th width="150">
					<center>Статус</center>
				</th>
				<th width="150">
					<center>Дата</center>
				</th>
			</tr>
		</thead>
		<tr>
			<td>{{ parse['t_id'] }}</td>
			<td>{{ parse['t_username'] }}</td>
			<td>{{ parse['t_subject'] }}</a></td>
			<td>{{ parse['t_statustext'] }}</td>
			<td>{{ parse['t_date'] }}</td>
		</tr>
	</table>
	<div class="separator"></div>
	<table class="table table-advance">
		<thead>
			<tr>
				<th>Текст запроса:</th>
			</tr>
		</thead>
		<tr>
			<td>{{ parse['t_text'] }}</td>
		</tr>
	</table>
	<div class="portlet box green">
		<div class="portlet-title">
			<div class="caption">Ответ</div>
		</div>
		<div class="portlet-body form">
			<form action="{{ url('admin/support/send/'~parse['t_id']~'/') }}" method="POST">
				<div class="form-body">
					<div class="form-group">
						<textarea class="form-control" rows="10" name="text" title=""></textarea>
					</div>
					<div class="form-actions">
						<button type="submit" class="btn green">Ответить</button>
					</div>
				</div>
			</form>
			<hr>
			{% if parse['t_status'] != 0 %}
			<form action="{{ url('admin/support/close/'~parse['t_id']~'/') }}" method="POST">
				<input type="submit" value="Закрыть"></form>
			{% else %}
			<form action="{{ url('admin/support/open/'~parse['t_id']~'/') }}" method="POST">
				<input type="submit" value="Открыть"></form>
			{% endif %}
		</div>
	</div>
{% endif %}