@extends(backpack_view('blank'))
@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">Переменные сервера</div>
				<div class="card-body table-responsive">
					<table class="table table-bordered table-striped">
						@foreach ($_SERVER as $key => $value)
							<tr>
								<th class="text-left">{{ $key }}</th>
								<td class="c text-left">{{ $value }}</td>
							</tr>
						@endforeach
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection