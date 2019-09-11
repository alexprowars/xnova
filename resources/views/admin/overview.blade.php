@extends('layouts.admin')
@section('content')
<div class="kt-portlet">
	<div class="kt-portlet__head">
		<div class="kt-portlet__head-label">
			<h3 class="kt-portlet__head-title">Активные игроки</h3>
		</div>
	</div>
	<div class="kt-portlet__body">
		<div class="alert alert-success">
			<div class="alert-text">Версия сервера: <strong class="text-danger">{{ $parse['adm_ov_data_yourv'] }}</strong></div>
		</div>
		<div class="kt-section__content">
			<div class="table-responsive">
				<table class="table table-hover table-light table-striped">
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
						@foreach ($parse['adm_ov_data_table'] as $list)
							<tr>
								<td>
									<a class="text-dark text-danger" href="{{ url('messages/write/'.$list['adm_ov_data_id'].'/') }}">
										<i class="flaticon2-email"></i>
									</a>
								</td>
								<td><a class="text-dark text-danger" href="{{ url('manager/data/username/'.$list['adm_ov_data_id'].'/send/') }}">{{ $list['adm_ov_data_name'] }}</a></td>
								<td><a style="color:{{ $list['adm_ov_data_clip'] }};" class="text-dark text-danger" href="http://network-tools.com/default.asp?prog=trace&host={{ $list['adm_ov_data_adip'] }}">{{ $list['adm_ov_data_adip'] }}</a></td>
								<td>{{ $list['adm_ov_data_ally'] }}</td>
								<td>{{ $list['adm_ov_data_activ'] }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="kt-portlet__foot">
		<div class="alert alert-info">
			<div class="alert-text">Игроков в сети: {{ $parse['adm_ov_data_count'] }}</div>
		</div>
	</div>
</div>
@endsection