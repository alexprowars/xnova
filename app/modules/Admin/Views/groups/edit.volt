<div class="portlet light bordered">
	<div class="portlet-body">
		<form action="{{ url('groups/edit/'~form.getValue('id')~'/') }}" method="post" id="{{ form.getFormId() }}" class="form-horizontal form" enctype="multipart/form-data">
			<div class="tabbable-line boxless tabbable-reversed">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#tab_info" data-toggle="tab">Информация</a>
					</li>
					<li>
						<a href="#tab_access" data-toggle="tab">Доступы</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="tab_info">
						{{ form.get('title').render() }}
					</div>
					<div class="tab-pane" id="tab_access">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>#</th>
									<th>Модуль</th>
									<th class="text-center">Управление</th>
									<th>Права доступа</th>
								</tr>
							</thead>
							<tbody>
								{% for module, items in access %}
									<tr>
										<td>{{ module }}</td>
										<td>{{ _text(module, 'module_name') }}</td>
										<td class="text-center">
											<div class="mt-checkbox-inline">
												<label class="mt-checkbox mt-checkbox-outline">
													<input type="checkbox" name="roles[{{ module }}][access]" value="{{ items['access'] }}" {{ (in_array(items['access'], access_group) ? 'checked' : '') }}>
													<span></span>
												</label>
											</div>
										</td>
										<td>
											{% for role, id in items if role != 'access'  %}
												<div class="mt-checkbox-inline">
													<label class="mt-checkbox mt-checkbox-outline">
														<input type="checkbox" name="roles[{{ module }}][{{ role }}]" value="{{ id }}" {{ (in_array(id, access_group) ? 'checked' : '') }}>
														{{ _text(module, 'module_access', role) }}
														<span></span>
													</label>
												</div>
											{% endfor %}
										</td>
									</tr>
								{% endfor %}
							</tbody>
						</table>
					</div>
				</div>
			</div>
			{{ form.renderActions() }}
			{{ form.renderValidation() }}
		</form>
	</div>
</div>