<form action="{{ url('options/') }}" method="post" enctype="multipart/form-data">
	<div class="card">
		<div class="card-body">
			<div class="tabbable-line boxless tabbable-reversed">
				{% set i = 0 %}
				<ul class="nav nav-tabs">
					{% for code, title in groups %}
						<li class="{{ (i == 0 ? 'active' : '') }}">
							<a href="#tab_{{ code }}" data-toggle="tab">{{ title }}</a>
						</li>
						{% set i = i + 1 %}
					{% endfor %}
				</ul>
				<div class="tab-content">
					{% set i = 0 %}
					{% for code, title in groups %}
						<div class="tab-pane {{ (i == 0 ? 'active' : '') }}" id="tab_{{ code }}">
							<div class="form-horizontal">
								{% for option in options if option.group_id == code %}
									<div class="form-group">
										<label class="col-md-3 control-label">{{ option.title }}</label>
										<div class="col-md-9">
											{% if option.type == 'string' %}
												<input name="option[{{ option.name }}]" type="text" class="form-control" placeholder="" value="{{ (option.value == null ? option.def : option.value) }}">
											{% elseif option.type == 'integer' %}
												<input name="option[{{ option.name }}]" type="number" class="form-control" placeholder="" value="{{ (option.value == null ? option.def : option.value) }}">
											{% elseif option.type == 'text' %}
												<textarea name="option[{{ option.name }}]" title="" class="form-control">{{ (option.value == null ? option.def : option.value) }}</textarea>
											{% elseif option.type == 'checkbox' %}
												<div class="mt-checkbox-inline">
													<label class="mt-checkbox mt-checkbox-outline">
														<input type="hidden" name="option[{{ option.name }}]" value="N">
														<input type="checkbox" name="option[{{ option.name }}]" value="Y" {{ ((option.value == 'Y' or (option.value == null and option.def == 'Y')) ? 'checked' : '') }}>
														<span></span>
													</label>
												</div>
											{% endif %}
											{% if option.description != "" %}
												<span class="help-inline">{{ option.description }}</span>
											{% endif %}
										</div>
									</div>
								{% endfor %}
							</div>
						</div>
						{% set i = i + 1 %}
					{% endfor %}
				</div>
			</div>
			{% if access.canWriteController('options', 'admin') %}
				<div class="form-actions">
					<div class="row">
						<div class="col-md-offset-3 col-md-9">
							<button type="submit" class="btn green">Сохранить</button>
						</div>
					</div>
				</div>
			{% endif %}
		</div>
	</div>
</form>