<div class="row">
	<div class="col-lg-6">
		<form action="{{ url('money/add/') }}" method="post" class="card">
			<div class="card-title card-title-bold">Начисление кредитов на счет</div>
			<div class="card-body">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">Логин или ID игрока</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" name="username" title="">
					</div>
				</div>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">Сумма</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" name="money" title="">
					</div>
				</div>
			</div>
			<footer class="card-footer text-right">
				<button class="btn btn-primary" type="submit">Начислить</button>
			</footer>
		</form>
	</div>
</div>