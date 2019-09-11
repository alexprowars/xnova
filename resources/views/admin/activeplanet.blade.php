<div class="card">
	<table class="table table-striped table-hover table-sm table-responsive">
		<thead>
			<tr>
				<th>Название</th>
				<th>Позиция</th>
				<th width="150">Активность</th>
			</tr>
		</thead>
		{% for planet in parse['rows'] %}
			<tr>
				<td>{{ planet['name'] }}</td>
				<td>{{ planet['position'] }}</td>
				<td>{{ pretty_time(planet['activity']) }}</td>
			</tr>
		{% endfor %}
	</table>

	<footer class="card-footer">
		<div class="row">
			<div class="col-md-5 col-sm-12">
				<div class="dataTables_info">
					Активно {{ parse['total'] }} планет{{ morph(parse['total'], 'feminine', 5) }}
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