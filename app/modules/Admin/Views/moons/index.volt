<div class="card">
	<header class="card-header">
		<div class="card-header-actions">
			<a href="{{ url('moons/add/') }}" class="btn btn-sm btn-primary">
				Создать
			</a>
		</div>
	</header>
	<table class="table table-striped table-hover table-sm table-responsive">
		<thead>
			<tr>
				<th>ID</th>
				<th>Название луны</th>
				<th>ID планеты</th>
				<th>Галактика</th>
				<th>Система</th>
				<th>Планета</th>
			</tr>
		</thead>
		{% for u in parse['moons'] %}
			<tr>
				<td>{{ u['id'] }}</td>
				<td>{{ u['name'] }}</td>
				<td>{{ u['parent_planet'] }}</td>
				<td>{{ u['galaxy'] }}</td>
				<td>{{ u['system'] }}</td>
				<td>{{ u['planet'] }}</td>
			</tr>
		{% endfor %}
	</table>

	<footer class="card-footer">
		<div class="row">
			<div class="col-md-5 col-sm-12">
				{% if parse['moons']|length == 1 %}
					В игре одна луна
				{% else %}
					В игре {{ parse['moons']|length }} лун{{ morph(parse['moons']|length, 'feminine', 5) }}
				{% endif %}
			</div>
		</div>
	</footer>
</div>