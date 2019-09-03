<div class="card">
	<table class="table table-striped table-hover table-sm">
		<thead>
			<tr>
				<th>#</th>
				<th>Название модуля</th>
				<th>Код модуля</th>
				<th>Системный</th>
				<th>Сортировка</th>
				<th>Действия</th>
			</tr>
		</thead>
		<tbody>
			{% for module in modules %}
				<tr>
					<td class="align-middle">
						{% if access.canWriteController('modules', 'admin') %}
							<a href="{{ url('modules/edit/'~module.id~'/') }}">{{ module.id }}</a>
						{% else %}
							{{ module.id }}
						{% endif %}
					</td>
					<td class="align-middle">{{ _text(module.code, 'module_name') }}</td>
					<td class="align-middle">{{ module.code }}</td>
					<td class="align-middle">{{ module.system }}</td>
					<td class="align-middle">{{ module.sort }}</td>
					<td class="align-middle">
						{% if access.canWriteController('modules', 'admin') %}
							{% if module.active == 'Y' %}
								<a href="javascript:;" {% if module.code != 'core' %}onclick="if (window.confirm('Деактивировать модуль?')) location.href='{{ url('modules/activate/'~module.id~'/N/') }}';"{% endif %} class="btn btn-sm btn-danger" {% if module.code == 'core' %}disabled{% endif %}><i class="fa fa-trash-o"></i> Деактивировать </a>
							{% else %}
								<a href="javascript:;" onclick="if (window.confirm('Активировать модуль?')) location.href='{{ url('modules/activate/'~module.id~'/Y/') }}';" class="btn btn-sm btn-secondary"><i class="fa fa-cogs"></i> Активировать </a>
							{% endif %}
						{% endif %}
					</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
</div>