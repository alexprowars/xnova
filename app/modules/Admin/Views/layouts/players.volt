<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
		<thead>
			<tr>
				<th><a href="{{ url('userlist/cmd/sort/type/id/') }}">ID</a></th>
				<th><a href="{{ url('userlist/cmd/sort/type/username/') }}">Логин игрока</a></th>
				<th><a href="{{ url('userlist/cmd/sort/type/email/') }}">E-Mail</a></th>
				<th><a href="{{ url('userlist/cmd/sort/type/ip/') }}">IP</a></th>
				<th><a href="{{ url('userlist/cmd/sort/type/create_time/') }}">Регистрация</a></th>
				<th><a href="{{ url('userlist/cmd/sort/type/banned/') }}">Блок</a></th>
			</tr>
		</thead>
		{% for list in parse['adm_ul_table'] %}
			<tr>
				<td><a href="/admin/manager/data/username/{{ list['adm_ul_data_id'] }}/">{{ list['adm_ul_data_id'] }}</a></td>
				<td><a href="/admin/manager/data/username/{{ list['adm_ul_data_id'] }}/">{{ list['adm_ul_data_name'] }}</a></td>
				<td>{{ list['adm_ul_data_mail'] }}</td>
				<td>{{ list['adm_ul_data_adip'] }}</td>
				<td>{{ list['adm_ul_data_regd'] }}<br>{{ list['adm_ul_data_lconn'] }}</td>
				<td>{{ list['adm_ul_data_banna'] }}</td>
			</tr>
		{% endfor %}
	</table>
</div>

<div class="row">
	<div class="col-md-12 col-sm-12">
		<div class="dataTables_paginate paging_bootstrap">
			{{ parse['adm_ul_count'] }}
		</div>
	</div>
</div>