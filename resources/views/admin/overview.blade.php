@extends(backpack_view('blank'))
@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>Панель управления</h1>
        </div>
    </section>
@endsection
@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					Активные игроки
				</div>
				<div class="card-body table-responsive">
					<div class="alert alert-success">
						Версия сервера: <strong class="text-danger">{{ $parse['adm_ov_data_yourv'] }}</strong>
					</div>
					<table class="table table-hover table-striped table-responsive-sm table-bordered">
						<thead>
							<tr>
								<th width="30"><a href="{{ url('overview/cmd/sort/type/id/') }}">&nbsp;</a></th>
								<th><a href="{{ url('overview/cmd/sort/type/username/') }}">Логин игрока</a></th>
								<th><a href="{{ url('overview/cmd/sort/type/ip/') }}">IP</a></th>
								<th><a href="{{ url('overview/cmd/sort/type/ally_name/') }}">Альянс</a></th>
								<th><a href="{{ url('overview/cmd/sort/type/onlinetime/') }}">Активность</a></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($parse['adm_ov_data_table'] as $list)
								<tr>
									<td>
										<a href="{{ url('messages/write/'.$list['adm_ov_data_id'].'/') }}">
											<i class="fa fa-envelope"></i>
										</a>
									</td>
									<td><a href="{{ url('manager/data/username/'.$list['adm_ov_data_id'].'/send/') }}">{{ $list['adm_ov_data_name'] }}</a></td>
									<td><a style="color:{{ $list['adm_ov_data_clip'] }};" href="http://network-tools.com/default.asp?prog=trace&host={{ $list['adm_ov_data_adip'] }}">{{ $list['adm_ov_data_adip'] }}</a></td>
									<td>{{ $list['adm_ov_data_ally'] }}</td>
									<td>{{ $list['adm_ov_data_activ'] }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="card-footer">
					<div class="alert alert-info">
						Игроков в сети: {{ $parse['adm_ov_data_count'] }}
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection