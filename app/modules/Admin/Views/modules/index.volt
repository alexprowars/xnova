<div class="util-btn-margin-bottom-5">
	<a href="{{ url('admin/modules/add/') }}">
		<button type="button" class="btn green btn-sm">Добавить</button>
	</a>
</div>
<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
		<thead>
			<tr>
				<th width="30">ID</th>
				<th>Алиас</th>
				<th>Название</th>
				<th>Административный</th>
				<th>Активность</th>
			</tr>
		</thead>
		{% for module in list %}
			<tr>
				<td><a href="/admin/modules/edit/{{ module['id'] }}/">{{ module['id'] }}</a></td>
				<td>{{ module['alias'] }}</td>
				<td>{{ module['name'] }}</td>
				<td>{{ module['is_admin'] }}</td>
				<td>{{ module['active'] }}</td>
			</tr>
		{% endfor %}
	</table>
</div>