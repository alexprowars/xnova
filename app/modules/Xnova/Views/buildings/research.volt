{% if parse['noresearch'] %}<div class="negative">{{ parse['noresearch'] }}</div>{% endif %}
<div class="block">
	<div class="title">Исследования</div>
	<div class="content">
		<div class="row research">
			{% for build in parse['technolist'] %}
				<div class="col-md-6 col-xs-12">
					<div class="viewport buildings {% if build['access'] is false %}shadow{% endif %}">
						{% if build['access'] is false %}
							<div class="notAvailable tooltip" data-content="Требования:<br>{{ replace('"', "'", getTechTree(build['i'], this.user, this.planet)) }}" onclick="showWindow('{{ _text('tech', build['i']) }}', '{{ url('info/'~(build['i'] > 300) ? (build['i'] < 350 ? (build['i'] - 100) : (build['i'] + 50)) : build['i']~'/') }}', 600)"><span>недоступно</span></div>
						{% endif %}

						<div class="img">
							<a href="javascript:;" onclick="showWindow('{{ _text('tech', build['i']) }}', '{{ url('info/'~((build['i'] > 300) ? (build['i'] < 350 ? (build['i'] - 100) : (build['i'] + 50)) : build['i'])~'/') }}', 600)">
								<img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ (build['i'] > 300) ? (build['i'] < 350 ? (build['i'] - 100) : (build['i'] + 50)) : build['i'] }}.gif" align="top" width="120" height="120" class="tooltip" data-content='<center>{{ _text('descriptions', build['i']) }}</center>' data-tooltip-width="150">
							</a>

							<div class="overContent">
								{{ build['tech_price'] }}
							</div>
						</div>
						<div class="title">
							<a href="{{ url('info/'~((build['i'] > 300) ? (build['i'] < 350 ? (build['i'] - 100) : (build['i'] + 50)) : build['i'])~'/') }}">{{ _text('tech', build['i']) }}</a>
						</div>
						<div class="actions">
							Уровень: {{ build['tech_level'] }}<br>

							{% if build['access'] %}
								Время: {{ pretty_time(build['search_time']) }}

								{% if build['add'] is defined %}
									<br><br>Бонусы:<br>{{ build['add'] }}
								{% endif %}
								<div class="startBuild">
									{% if build['tech_link'] is type('array') %}
										<div id="brp" class="z"></div>
										<script type="text/javascript">
											var v = new Date();
											var brp = $('#brp');

											function t()
											{
												var n = new Date();
												var s = {{ build['tech_link']['tech_time'] }} - Math.round((n.getTime() - v.getTime()) / 1000);
												var m = 0;
												var h = 0;

												if (s < 0)
													brp.html('<a href="javascript:;" onclick="load(\'{{ url('buildings/'~parse['mode']~'/?chpl='~build['tech_link']['tech_home']~'') }}\')">завершено. продолжить...</a>');
												else
												{
													if (s > 59)
													{
														m = Math.floor(s / 60);
														s = s - m * 60;
													}
													if (m > 59)
													{
														h = Math.floor(m / 60);
														m = m - h * 60;
													}
													if (s < 10)
														s = "0" + s;
													if (m < 10)
														m = "0" + m;

													brp.html(h + ':' + m + ':' + s + '&nbsp;<a href="javascript:;" onclick="load(\'{{ url('buildings/'~parse['mode']~'/cmd/cancel/tech/'~build['tech_link']['tech_id']~'/') }}\')">Отменить{{ build['tech_link']['tech_name'] }}</a>');

													setTimeout(t, 1000);
												}
											}
											$(document).ready(t);
										</script>
									{% else %}
										{{ build['tech_link'] }}
									{% endif %}
								</div>
							{% endif %}
						</div>
					</div>
				</div>
			{% endfor %}
		</div>
	</div>
</div>