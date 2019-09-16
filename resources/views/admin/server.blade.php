@extends(backpack_view('blank'))
@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>Переменные сервера</h1>
        </div>
    </section>
@endsection
@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
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