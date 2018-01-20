<div id="tabs" class="ui-tabs ui-widget ui-widget-content techtree">
	<div class="head">
		<ul class="ui-tabs-nav ui-widget-header">
			{% for i, list in parse %}
				{% if list['required_list'] is not defined %}
					<li><a href="#tabs-{{ i }}">{{ list['tt_name'] }}</a></li>
				{% endif %}
			{% endfor %}
		</ul>
	</div>
	<div id="tabs-0" class="ui-tabs-panel ui-widget-content container-fluid">
		{% for i, list in parse if i > 0 %}
			{% if list['required_list'] is not defined %}
				</div><div id="tabs-{{ i }}" class="ui-tabs-panel ui-widget-content container-fluid" style="display: none">
			{% else %}
				<div class="row">
					<div class="col-sm-5 col-6 title">
						<a href="{{ url('info/'~list['tt_info']~'/') }}">{{ list['tt_name'] }}</a>

						{% if list['required_list'] != '' %}
							<div class="float-right d-none d-sm-block"><a href="{{ url('tech/'~list['tt_info']~'/') }}">[i]</a></div>
						{% endif %}
					</div><div class="col-sm-7 col-6">
						{{ list['required_list'] }}
					</div>
				</div>
			{% endif %}
		{% endfor %}
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		$("#tabs").tabs();
		$("#tabs .row:even").addClass("odd");
	});
</script>