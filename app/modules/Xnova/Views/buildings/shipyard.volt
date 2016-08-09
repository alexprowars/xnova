<div class="block">
	<div class="content">
		<form action="{{ url('buildings/'~parse['mode']~'/') }}" method="post">
			<div class="row shipyard">
				<div class="col-xs-12 c">
					<input type="submit" value="Построить">
				</div>
				{% for build in parse['buildlist'] %}
					<div class="col-md-6 col-xs-12">
						<div class="viewport buildings {% if build['access'] is false %}shadow{% endif %}">
							{% if build['access'] is false %}
								<div class="notAvailable tooltip" data-content="Требования:<br>{{ replace('"', "'", getTechTree(build['i'], this.user, this.planet)) }}" onclick="showWindow('{{ _text('xnova', 'tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}/', 600)"><span>недоступно</span></div>
							{% endif %}

							<div class="img">
								<a href="javascript:;" onclick="showWindow('{{ _text('xnova', 'tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}', 600)">
									<img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ build['i'] }}.gif" alt='{{ _text('xnova', 'tech', build['i']) }}' align="top" width="120" height=120 class="tooltip" data-content='<center>{{ _text('xnova', 'descriptions', build['i']) }}</center>' data-tooltip-width="150">
								</a>

								<div class="overContent">
									{{ build['price'] }}
								</div>
							</div>
							<div class="title">
								<a href="?{{ url('info/'~build['i']~'/') }}">{{ _text('xnova', 'tech', build['i']) }}</a> (<span class="{{ build['count'] > 0 ? 'positive' : 'negative' }}">{{ pretty_number(build['count']) }}</span>)
							</div>
							<div class="actions">
								{% if build['access'] %}
									Время: {{ pretty_time(build['time']) }}
									{% if build['add'] != '' %}
										{{ build['add'] }}
									{% else %}
										<br>
									{% endif %}
									{% if build['can_build'] %}
										{% if build['maximum'] %}
											<br>
											<center><font color="red">Вы можете построить только {{ build['max'] }} постройку данного типа</font></center>
										{% else %}
											<br>
											<a href=javascript:setMaximum({{ build['i'] }},{{ build['max'] }});>Максимум: <font color="lime">{{ build['max'] }}</font></a>
											<div class="buildmax">
												<input type="number" name="fmenge[{{ build['i'] }}]" alt="{{ _text('xnova', 'tech', build['i']) }}" style="max-width: 80px" maxlength="5" value="" placeholder="0">
											</div>
										{% endif %}
									{% endif %}
								{% endif %}
							</div>
						</div>
					</div>
				{% endfor %}
				<div class="col-xs-12 c">
					<input type="submit" value="Построить">
				</div>
			</div>
		</form>
	</div>
</div>