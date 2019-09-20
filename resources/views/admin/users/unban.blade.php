@extends(backpack_view('blank'))
@section('content')
	<div class="row">
		<div class="col-12">
			@include('admin.components.flash-message')
			<div class="card">
				<div class="card-header">
					Форма разбана
				</div>
				<div class="card-body">
					<form action="" method="post" class="form-horizontal">
						<div class="form-group row">
							<label class="col-md-3 col-form-label">Логин игрока</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="username">
							</div>
						</div>
						<div class="form-actions">
							<button type="submit" class="btn btn-success">Разбанить</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection