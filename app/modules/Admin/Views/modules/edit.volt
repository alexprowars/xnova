<div class="portlet light bordered">
	<div class="portlet-body form">
		<form action="{{ url('modules/edit/'~form.getValue('id')~'/') }}" method="post" class="form-horizontal">
			<div class="form-group">
				<label class="col-md-3 control-label">Код</label>
				<div class="col-md-4">
					<p class="form-control-static">{{ form.getValue('code') }}</p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label">Сортировка</label>
				<div class="col-md-4">
					<input name="sort" type="text" class="form-control" placeholder="Введите сортировку" value="{{ form.getValue('sort') }}">
				</div>
			</div>
			<div class="form-actions">
				<div class="row">
					<div class="col-md-offset-3 col-md-4">
						<button type="submit" class="btn green">Сохранить</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>