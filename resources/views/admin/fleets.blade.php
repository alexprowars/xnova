@extends(backpack_view('blank'))
@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>ID</th>
								<th>Состав флота</th>
								<th>Задание</th>
								<th>Владелец</th>
								<th>Планета-дом</th>
								<th>Время отправления</th>
								<th>Игрок-цель</th>
								<th>Планета-цель</th>
								<th>Время на орбите</th>
								<th>Время прибытия</th>
							</tr>
						</thead>
						@foreach ($items as $fleet)
							<tr>
								<td>{{ $fleet['Id'] }}</td>
								<td>{{ $fleet['Fleet'] }}</td>
								<td>{{ $fleet['Mission'] }}</td>
								<td>{{ $fleet['St_Owner'] }}</td>
								<td>{{ $fleet['St_Posit'] }}</td>
								<td>{{ $fleet['St_Time'] }}</td>
								<td>{{ $fleet['En_Owner'] }}</td>
								<td>{{ $fleet['En_Posit'] }}</td>
								<td>{{ $fleet['St_Time'] }}</td>
								<td>{{ $fleet['En_Time'] }}</td>
							</tr>
						@endforeach
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection