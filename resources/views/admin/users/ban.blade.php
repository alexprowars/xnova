@extends(backpack_view('blank'))
@section('content')
	<div class="row">
		<div class="col-12">
			@include('admin.components.flash-message')
			<div class="card">
				<div class="card-header">
					Блокировка доступа
				</div>
				<div class="card-body">
					<form action="" method="post" class="form-horizontal">
						<input type="hidden" name="modes" value="banit">
						<div class="form-group row">
							<label class="col-md-3 col-form-label">Логин</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="name" title="">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-3 col-form-label">Причина</label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="why" title="">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-3 col-form-label">Время бана</label>
							<div class="col-md-9">
								<div class="row">
									<div class="col-md-3"><input name="days" type="text" value="" size="5" class="form-control" placeholder="д"></div>
									<div class="col-md-3"><input name="hour" type="text" value="" size="5" class="form-control" placeholder="ч"></div>
									<div class="col-md-3"><input name="mins" type="text" value="" size="5" class="form-control" placeholder="м"></div>
									<div class="col-md-3"><input name="secs" type="text" value="" size="5" class="form-control" placeholder="с"></div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-3 control-label">Режим отпуска</label>
							<div class="col-md-9">
								<input name="ro" type="checkbox" value="1" class="form-control">
							</div>
						</div>
						<div class="form-actions">
							<button type="submit" class="btn btn-success">Забанить</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection