<div class="card">
	<header class="card-header">
		<div class="card-header-actions">
			{% if access.canWriteController('groups', 'admin') %}
				<a href="{{ url('groups/add/') }}" class="btn btn-sm btn-primary">Добавить</a>
			{% endif %}
		</div>
	</header>
	<table class="table table-striped table-hover table-sm">
		<thead>
			<tr>
				<th width="10%">#</th>
				<th>Название группы</th>
				<th width="200">Действия</th>
			</tr>
		</thead>
		<tbody>
			{% for group in groups %}
				<tr>
					<td class="align-middle">
						{% if access.canWriteController('groups', 'admin') %}
							<a href="{{ url('groups/edit/'~group.id~'/') }}">{{ group.id }}</a>
						{% else %}
							{{ group.id }}
						{% endif %}
					</td>
					<td class="align-middle">{{ group.title }}</td>
					<td class="align-middle">
						<div class="actions">
							{% if access.canWriteController('groups', 'admin') %}
								<a href="{{ url('groups/edit/'~group.id~'/') }}" class="btn btn-outline btn-sm btn-warning"><i class="fa fa-edit"></i> Изменить </a>
								{% if !group.isSystem() %}
									<a href="javascript:;" onclick="if (window.confirm('Удалить группу')) location.href='{{ url('groups/delete/'~group.id~'/') }}';" class="btn btn-outline btn-sm btn-danger"><i class="fa fa-trash-o"></i> Удалить </a>
								{% endif %}
							{% endif %}
						</div>
					</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
</div>