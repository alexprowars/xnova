{% if info is defined %}
	<div class="portlet box green">
		<div class="portlet-title">
			<div class="caption">Редактирование пользователя "{{ info['username'] }}"</div>
		</div>
		<div class="portlet-body form">
			<form action="{{ url('admin/users/mode/edit/id/'~info['id']~'/') }}" method="post" class="form-horizontal form-row-seperated">
				<div class="form-body">
					<div class="form-group">
						<label class="col-md-3 control-label">Имя</label>
						<div class="col-md-9">
							<input type="text" class="form-control" name="username" value="{{ info['username'] }}" title="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-3 control-label">Группа</label>
						<div class="col-md-9">
							<select class="form-control" name="group_id" title="">
								<option value="0">Без группы</option>
								{% for group in groups %}
									<option value="{{ group['id'] }}" {{ group['id'] == info['group_id'] ? 'selected' : '' }}>{{ group['name'] }}</option>
								{% endfor %}
							</select>
						</div>
					</div>
					<div class="form-actions">
						<button type="submit" name="save" class="btn green" value="Y">Сохранить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
{% endif %}