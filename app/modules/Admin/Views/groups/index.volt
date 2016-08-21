<div class="portlet light bordered">
	<div class="portlet-body">
		<div class="table-toolbar">
			<div class="row">
				<div class="col-md-6">
					<div class="btn-group">
						{% if access.canWriteController('groups', 'admin') %}
							<a href="{{ url('groups/add/') }}" class="btn sbold green">Добавить <i class="fa fa-plus"></i></a>
						{% endif %}
					</div>
				</div>
			</div>
		</div>
		<div class="table-container">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>#</th>
						<th>Название группы</th>
						<th width="250">Действия</th>
					</tr>
				</thead>
				<tbody>
					{% for group in groups %}
						<tr>
							<td>
								{% if access.canWriteController('groups', 'admin') %}
									<a href="{{ url('groups/edit/'~group.id~'/') }}">{{ group.id }}</a>
								{% else %}
									{{ group.id }}
								{% endif %}
							</td>
							<td>{{ group.title }}</td>
							<td>
								{% if access.canWriteController('groups', 'admin') %}
									<a href="{{ url('groups/edit/'~group.id~'/') }}" class="btn btn-outline btn-sm purple"><i class="fa fa-edit"></i> Изменить </a>
									{% if !group.isSystem() %}
										<a href="javascript:;" onclick="if (window.confirm('Удалить группу')) location.href='{{ url('groups/delete/'~group.id~'/') }}';" class="btn btn-outline dark btn-sm black"><i class="fa fa-trash-o"></i> Удалить </a>
									{% endif %}
								{% endif %}
							</td>
						</tr>
					{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>