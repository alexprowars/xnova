<form action="{{ url('admin/messages/') }}" method="post" class="form-horizontal">
	<input type="hidden" name="curr" value="{{ parse['mlst_data_page'] }}">
	<input type="hidden" name="pmax" value="{{ parse['mlst_data_pagemax'] }}">
	<input type="hidden" name="sele" value="{{ parse['mlst_data_sele'] }}">
	<div class="table-toolbar">
		<div class="portlet box green">
			<div class="portlet-title">
				<div class="caption">Фильтры</div>
				<div class="tools">
					<a href="" class="collapse"></a>
				</div>
			</div>
			<div class="portlet-body form">
				<div class="form-body">
					<div class="form-group">
						<div class="col-md-5 text-xs-right">
							<input type="submit" name="prev"  class="btn green" value="{{ _text('admin', 'mlst_hdr_prev') }}">
						</div>
						<div class="col-md-2 text-xs-center">
							<select name="page" class="form-control" onchange="submit();" title="">
								{% for cPage in 1..parse['mlst_data_pagemax'] %}
									<option value="{{ cPage }}" {{ parse['mlst_data_page'] == cPage ? "selected" : "" }}>{{ cPage }}/{{ parse['mlst_data_pagemax'] }}</option>
								{% endfor %}
							</select>
						</div>
						<div class="col-md-5 text-xs-left">
							<input type="submit" name="next" class="btn green" value="{{ _text('admin', 'mlst_hdr_next') }}"/>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-6 text-xs-center">
							<input type="text" placeholder="owner" class="form-control form-control-inline input-small" name="userid" size="7" value="{{ parse['userid'] is defined ? parse['userid'] : '' }}"/>
							<input type="text" placeholder="sender" class="form-control form-control-inline input-small" name="userid_s" size="7" value="{{ parse['userid_s'] is defined ? parse['userid_s'] : '' }}"/>
							<input type="submit" name="usersearch" class="btn red" value="По id"/>
						</div>
						<div class="col-md-6 text-xs-center">
							<select name="type" class="form-control" onchange="submit();" title="">
								{% for type in parse['types'] %}
									<option value="{{ type }}"{{ parse['mlst_data_sele'] == type ? " SELECTED" : "" }}>{{ _text('admin', 'mlst_mess_typ__'~type) }}</option>
								{% endfor %}
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-4 text-xs-center">
							<input type="submit" name="delsel" class="btn red" value="{{ _text('admin', 'mlst_bt_delsel') }}"/>
						</div>
						<div class="col-md-8 text-xs-center">
							<input type="submit" name="deldat"  class="btn red" value="{{ _text('admin', 'mlst_bt_deldate') }}"/>
							<input type="text" placeholder="дд" name="selday" size="3" class="form-control form-control-inline input-small">
							<input type="text" placeholder="мм" name="selmonth" size="3" class="form-control form-control-inline input-small">
							<input type="text" placeholder="гггг" name="selyear" size="6" class="form-control form-control-inline input-small">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-striped table-advance">
			<thead>
				<tr>
					<th width="40">&nbsp;</th>
					<th>{{ _text('admin', 'mlst_hdr_time') }}</th>
					<th>{{ _text('admin', 'mlst_hdr_from') }}</th>
					<th>{{ _text('admin', 'mlst_hdr_to') }}</th>
					<th width="300">{{ _text('admin', 'mlst_hdr_text') }}</th>
				</tr>
			</thead>
			{% for list in parse['mlst_data_rows'] %}
			<tr>
				<td><input type="checkbox" name="sele_mes[{{ list['mlst_id'] }}]" title=""></td>
				<td>{{ list['mlst_time'] }}</td>
				<td>{{ list['mlst_from'] }}</td>
				<td>{{ list['mlst_to'] }}</td>
				<td width="300">{{ list['mlst_text'] }}</td>
			</tr>
			{% endfor %}
		</table>
	</div>
</form>
