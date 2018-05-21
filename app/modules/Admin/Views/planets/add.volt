<div class="row">
	<div class="col-lg-6">
		<form action="{{ url('planets/add/') }}" method="post" class="card">
			<div class="card-title card-title-bold">Создать планету</div>
			<div class="card-body">
				<div class="form-group row">
					<label class="col-md-4 col-form-label">Галактика</label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="galaxy" title="">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-md-4 col-form-label">Система</label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="system" title="">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-md-4 col-form-label">Планета</label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="planet" title="">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-md-4 col-form-label">user ID</label>
					<div class="col-md-8">
						<input type="text" class="form-control" name="user" title="">
					</div>
				</div>
			</div>
			<footer class="card-footer text-right">
				<button class="btn btn-primary" type="submit">Создать</button>
			</footer>
		</form>
	</div>
</div>