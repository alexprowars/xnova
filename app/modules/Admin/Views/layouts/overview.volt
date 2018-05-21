<div class="card">
	<h4 class="card-title">Активные игроки</h4>

	<div class="card-body">
		<div class="alert alert-success">Версия сервера: <strong class="text-danger">{{ parse['adm_ov_data_yourv'] }}</strong></div>

		<table class="table table-striped table-hover table-sm table-responsive">
			<thead class="thead-default">
				<tr>
					<th width="30"><a href="{{ url('overview/cmd/sort/type/id/') }}">&nbsp;</a></th>
					<th><a class="text-dark text-danger" href="{{ url('overview/cmd/sort/type/username/') }}">Логин игрока</a></th>
					<th><a class="text-dark text-danger" href="{{ url('overview/cmd/sort/type/ip/') }}">IP</a></th>
					<th><a class="text-dark text-danger" href="{{ url('overview/cmd/sort/type/ally_name/') }}">Альянс</a></th>
					<th><a class="text-dark text-danger" href="{{ url('overview/cmd/sort/type/onlinetime/') }}">Активность</a></th>
				</tr>
			</thead>
			<tbody>
				{% for list in parse['adm_ov_data_table'] %}
					<tr>
						<td><a class="text-dark text-danger" href="{{ url('messages/write/'~list['adm_ov_data_id']~'/') }}"><span class="fa fa-envelope-o"></span></a></td>
						<td><a class="text-dark text-danger" href="{{ url('manager/data/username/'~list['adm_ov_data_id']~'/send/') }}">{{ list['adm_ov_data_name'] }}</a></td>
						<td><a style="color:{{ list['adm_ov_data_clip'] }};" class="text-dark text-danger" href="http://network-tools.com/default.asp?prog=trace&host={{ list['adm_ov_data_adip'] }}">{{ list['adm_ov_data_adip'] }}</a></td>
						<td>{{ list['adm_ov_data_ally'] }}</td>
						<td>{{ list['adm_ov_data_activ'] }}</td>
					</tr>
				{% endfor %}
			</tbody>
		</table>

		<div class="alert alert-secondary">
			Игроков в сети: {{ parse['adm_ov_data_count'] }}
		</div>
	</div>
</div>