<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">{{ _text('adm_mod_level') }}</div>
	</div>
	<div class="portlet-body form">
		<form action="{{ url('admin/manager/level/') }}" method="post" class="form-horizontal form-bordered">
			<input type="hidden" name="send" value="y">
			<div class="form-body">
				<div class="form-group">
					<label class="col-md-3 control-label">{{ _text('adm_player_nm') }}</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="player" title="">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">{{ _text('adm_mess_lvl1') }}</label>
					<div class="col-md-9">
						<select class="form-control" name="authlvl" title="">
							{% for id, level in _text('user_level') %}
								<option value="{{ id }}">{{ level }}</option>
							{% endfor %}
						</select>
					</div>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn green">{{ _text('adm_bt_change') }}</button>
				</div>
			</div>
		</form>
	</div>
</div>