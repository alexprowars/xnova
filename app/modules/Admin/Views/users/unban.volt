<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">Форма разбана</div>
	</div>
	<div class="portlet-body form">
		<form action="{{ url('users/unban/') }}" method="post" class="form-horizontal form-bordered">
			<div class="form-body">
				<div class="form-group">
					<label class="col-md-3 control-label">Логин игрока</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="username" title="">
					</div>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn green">Разбанить</button>
				</div>
			</div>
		</form>
	</div>
</div>