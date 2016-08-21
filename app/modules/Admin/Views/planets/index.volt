<div class="util-btn-margin-bottom-5">
	<a href="{{ url('admin/planetlist/add/') }}">
		<button type="button" class="btn blue btn-sm">Создать</button>
	</a>
</div>
<div class="clearfix"></div>
<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
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
				<td class="b text-center">{{ planet['id'] }}</td>
				<td class="b text-center">{{ planet['name'] }}</td>
				<td class="b text-center">{{ planet['galaxy'] }}</td>
				<td class="b text-center">{{ planet['system'] }}</td>
				<td class="b text-center">{{ planet['planet'] }}</td>
				<td class="b text-center">{{ planetLink(planet) }}</td>
			</tr>
		{% endfor %}
	</table>
</div>
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