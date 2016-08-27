<div class="portlet box green">
	<div class="portlet-title">
		<div class="caption">{{ _text('admin', 'adm_search_ip') }}</div>
	</div>
	<div class="portlet-body form">
		<form action="{{ url('manager/ip/') }}" method="post" class="form-horizontal form-bordered">
			<input type="hidden" name="send" value="y">
			<div class="form-body">
				<div class="form-group">
					<label class="col-md-3 control-label">{{ _text('admin', 'adm_ip') }}</label>
					<div class="col-md-9">
						<input type="text" class="form-control" name="ip" title="">
					</div>
				</div>
				<div class="form-actions">
					<button type="submit" class="btn green">{{ _text('admin', 'adm_bt_search') }}</button>
				</div>
			</div>
		</form>
	</div>
</div>
