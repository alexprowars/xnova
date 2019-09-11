<div class="card">
	<header class="card-header">
		<div class="card-header-actions">
			<a href="{{ url('planets/add/') }}" class="btn btn-sm btn-primary">
				Создать
			</a>
		</div>
	</header>
	<table class="table table-striped table-hover table-sm table-responsive">
		<thead>
			<tr>
				<th width="50">ID</th>
				<th>Название планеты</th>
				<th>Галактика</th>
				<th>Система</th>
				<th>Планета</th>
				<th>Переход</th>
			</tr>
		</thead>
		{% for planet in planetlist %}
			<tr>
				<td>{{ planet['id'] }}</td>
				<td>{{ planet['name'] }}</td>
				<td>{{ planet['galaxy'] }}</td>
				<td>{{ planet['system'] }}</td>
				<td>{{ planet['planet'] }}</td>
				<td>{{ planetLink(planet) }}</td>
			</tr>
		{% endfor %}
	</table>

	<footer class="card-footer">
		<div class="row">
			<div class="col-md-5 col-sm-12">
				<div class="dataTables_info">
					В игре <b>{{ all }}</b> планет{{ morph(all, 'feminine', 5) }}
				</div>
			</div>
			<div class="col-md-7 col-sm-12">
				<div class="dataTables_paginate paging_bootstrap">
					{{ pagination }}
				</div>
			</div>
		</div>
	</footer>
</div>