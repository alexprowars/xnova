@extends(backpack_view('blank'))
@section('header')
	<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none">
		<h1 class="text-capitalize mb-0">Сообщения</h1>
	</section>
@endsection
@section('content')
	<form action="{{ backpack_url('/messages/') }}" method="post" class="form-horizontal">
		<input type="hidden" name="curr" value="{{ $parse['mlst_data_page'] }}">
		<input type="hidden" name="pmax" value="{{ $parse['mlst_data_pagemax'] }}">
		<input type="hidden" name="sele" value="{{ $parse['mlst_data_sele'] }}">

		<div class="row">
			<div class="col-lg-6">
				<div class="card">
					<div class="card-title card-title-bold">Фильтры</div>
					<div class="card-body">
						<div class="form-group row">
							<div class="col-8 text-center">
								<div class="row">
									<div class="col">
										<input type="text" placeholder="owner id" class="form-control" name="userid" size="7" value="{{ $parse['userid'] ?? '' }}"/>
									</div>
									<div class="col">
										<input type="text" placeholder="sender id" class="form-control" name="userid_s" size="7" value="{{ $parse['userid_s'] ?? '' }}"/>
									</div>
									<div class="col">
										<input type="submit" name="usersearch" class="btn btn-danger" value="По id">
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<select name="type" class="form-control" onchange="submit();" title="">
								@foreach($parse['types'] as $type)
									<option value="{{ $type }}"{{ $parse['mlst_data_sele'] == $type ? " SELECTED" : "" }}>{{ __('admin/messages.mlst_mess_typ__' . $type) }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="card">
					<div class="card-title card-title-bold">Действия</div>
					<div class="card-body">
						<div class="form-group">
							<input type="submit" name="delsel" class="btn btn-danger" value="{{ __('admin/messages.mlst_bt_delsel') }}"/>
						</div>
						<hr>
						<div class="form-group row">
							<div class="col">
								<input type="text" placeholder="дд" name="selday" size="3" class="form-control">
							</div>
							<div class="col">
								<input type="text" placeholder="мм" name="selmonth" size="3" class="form-control">
							</div>
							<div class="col">
								<input type="text" placeholder="гггг" name="selyear" size="6" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<input type="submit" name="deldat"  class="btn btn-danger" value="{{ __('admin/messages.mlst_bt_deldate') }}">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<table class="table table-striped table-hover table-sm table-responsive">
				<thead>
					<tr>
						<th width="40">&nbsp;</th>
						<th>{{ __('admin/messages.mlst_hdr_time') }}</th>
						<th>{{ __('admin/messages.mlst_hdr_from') }}</th>
						<th>{{ __('admin/messages.mlst_hdr_to') }}</th>
						<th width="300">{{ __('admin/messages.mlst_hdr_text') }}</th>
					</tr>
				</thead>
				@foreach($parse['items'] as $item)
					<tr>
						<td><input type="checkbox" name="message[{{ $item['id'] }}]"></td>
						<td>{{ $item['time'] }}</td>
						<td>{{ $item['from'] }}</td>
						<td>{{ $item['to'] }}</td>
						<td width="300">{!! $item['text'] !!}</td>
					</tr>
				@endforeach
			</table>
			<footer class="card-footer">
				<div class="row">
					<div class="col">
						{!! $pagination !!}
					</div>
				</div>
			</footer>
		</div>
	</form>
@endsection