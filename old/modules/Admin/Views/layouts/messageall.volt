<div class="card">
	<div class="card-title card-title-bold">
		Отправить сообщение всем игрокам
	</div>
	<form action="{{ url('messageall/') }}" method="post">
		<div class="card-body">
			<div class="form-group row">
				<label class="col-md-4 col-form-label">Тема сообщения</label>
				<div class="col-md-8">
					<input type="text" class="form-control" name="temat" title="">
				</div>
			</div>
			<div class="form-group row">
				<label class="col-md-4 col-form-label">Сообщение</label>
				<div class="col-md-8">
					<textarea name="tresc" cols="" rows="10" class="form-control" title=""></textarea>
				</div>
			</div>
		</div>
		<footer class="card-footer text-right">
			<button class="btn btn-primary" type="submit">Отправить</button>
		</footer>
	</form>
</div>