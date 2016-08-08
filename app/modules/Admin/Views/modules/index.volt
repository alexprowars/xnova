<table class="table table-hover">
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
				<td>
					{% if access.canWriteController('modules', 'admin') %}
						<a href="{{ url('modules/edit/'~module.id~'/') }}">{{ module.id }}</a>
					{% else %}
						{{ module.id }}
					{% endif %}
				</td>
				<td>{{ _text(module.code, 'module_name') }}</td>
				<td>{{ module.code }}</td>
				<td>{{ module.system }}</td>
				<td>{{ module.sort }}</td>
				<td>
					{% if access.canWriteController('modules', 'admin') %}
						{% if module.active == 'Y' %}
							<a href="javascript:;" {% if module.code != 'core' %}onclick="if (window.confirm('Деактивировать модуль?')) location.href='{{ url('modules/activate/'~module.id~'/N/') }}';"{% endif %} class="btn red" {% if module.code == 'core' %}disabled{% endif %}><i class="fa fa-trash-o"></i> Деактивировать </a>
						{% else %}
							<a href="javascript:;" onclick="if (window.confirm('Активировать модуль?')) location.href='{{ url('modules/activate/'~module.id~'/Y/') }}';" class="btn green"><i class="fa fa-cogs"></i> Активировать </a>
						{% endif %}
					{% endif %}
				</td>
			</tr>
		{% endfor %}
	</tbody>
</table>