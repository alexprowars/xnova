<form action="{{ url('options/') }}" method="post" enctype="multipart/form-data">
	<div class="card">
		<div class="card-body">
			<div class="tabbable-line boxless tabbable-reversed">
				{% set i = 0 %}
				<ul class="nav nav-tabs">
					{% for code, title in groups %}
						<li class="nav-item">
							<a class="nav-link {{ (i == 0 ? 'active' : '') }}" href="#tab_{{ code }}" data-toggle="tab">{{ title }}</a>
						</li>
						{% set i = i + 1 %}
					{% endfor %}
				</ul>
				<div class="tab-content">
					{% set i = 0 %}
					{% for code, title in groups %}
						<div class="tab-pane fade {{ (i == 0 ? 'active show' : '') }}" id="tab_{{ code }}">
							{% for option in options if option.group_id == code %}
								<div class="form-group row">
									<label class="col-sm-4 col-form-label">{{ option.title }}</label>
									<div class="col-sm-8">
										{% if option.type == 'string' %}
											<input name="option[{{ option.name }}]" type="text" class="form-control" placeholder="" value="{{ (option.value == null ? option.def : option.value) }}">
										{% elseif option.type == 'integer' %}
											<input name="option[{{ option.name }}]" type="number" class="form-control" placeholder="" value="{{ (option.value == null ? option.def : option.value) }}">
										{% elseif option.type == 'text' %}
											<textarea name="option[{{ option.name }}]" title="" class="form-control">{{ (option.value == null ? option.def : option.value) }}</textarea>
										{% elseif option.type == 'checkbox' %}
											<div class="custom-controls-stacked">
												<label class="custom-control custom-checkbox">
													<input type="hidden" name="option[{{ option.name }}]" value="N">
													<input type="checkbox" class="custom-control-input" name="option[{{ option.name }}]" value="Y" {{ ((option.value == 'Y' or (option.value == null and option.def == 'Y')) ? 'checked' : '') }}>
													<span class="custom-control-indicator"></span>
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
						{% set i = i + 1 %}
					{% endfor %}
				</div>
			</div>
		</div>
		{% if access.canWriteController('options', 'admin') %}
			<footer class="card-footer text-right">
				<button class="btn btn-primary" type="submit">Сохранить</button>
			</footer>
		{% endif %}
	</div>
</form>