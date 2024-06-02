@extends(backpack_view('blank'))
@section('header')
	<section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none">
		<h1 class="text-capitalize mb-0">Рассылка</h1>
	</section>
@endsection
@section('content')
	<div class="row">
		<div class="col-12">
			@include('admin.components.flash-message')
			<form action="" method="post">
				<div class="card">
					<div class="card-header">
						Отправить сообщение всем игрокам
					</div>
					<div class="card-body form-horizontal">
						<div class="form-group row">
							<label class="col-md-4 col-form-label">Тема сообщения</label>
							<div class="col-md-8">
								<input type="text" class="form-control" name="theme">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-4 col-form-label">Сообщение</label>
							<div class="col-md-8">
								<textarea name="message" cols="" rows="10" class="form-control"></textarea>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<button class="btn btn-primary" type="submit">Отправить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
@endsection