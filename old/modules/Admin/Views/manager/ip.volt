<div class="row">
	<div class="col-lg-6">
		<div class="card">
			<form action="{{ url('manager/ip/') }}" method="post" class="form-horizontal form-bordered">
				<div class="card-title card-title-bold">
					{{ _text('admin', 'adm_search_ip') }}
				</div>
				<div class="card-body">
					<input type="hidden" name="send" value="y">
					<div class="form-group row">
						<label class="col-sm-4 col-form-label">{{ _text('admin', 'adm_ip') }}</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" name="ip" title="">
						</div>
					</div>
				</div>
				<footer class="card-footer text-right">
					<button class="btn btn-primary" type="submit">{{ _text('admin', 'adm_bt_search') }}</button>
				</footer>
			</form>
		</div>
	</div>
</div>