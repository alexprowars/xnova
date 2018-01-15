<div class="block">
	<div class="title">
		Занято полей
		<span class="positive">{{ parse['planet_field_current'] }}</span> из <span class="negative">{{ parse['planet_field_max'] }}</span>
		<div class="pull-xs-right col-xs-12 col-sm-6 p-a-0">Осталось <span class="positive">{{ parse['field_libre'] }}</span> свободн{{ morph(parse['field_libre'], 'neuter', 2) }} пол{{ morph(parse['field_libre'], 'neuter', 1) }}</div>
		<div class="clearfix"></div>
	</div>
	{% for list in parse['BuildList'] %}
		<table class="table" id="building">
			<tr>
				<td class="c" width="50%">
					{{ list['ListID'] }}: {{ list['ElementTitle'] }} {{ list['BuildLevel'] }}{% if list['BuildMode'] != 'build' %}. {{ _text('xnova', 'destroy') }}{% endif %}
				</td>
				<td class="k">
					{% if list['ListID'] == 1 %}
						<div id="blc" class="z"></div>
						<script type="text/javascript">BuildTimeout({{ list['BuildTime'] }}, {{ list['ListID'] }}, {{ list['PlanetID'] }}, {{ session.has('LAST_ACTION_TIME') ? session.get('LAST_ACTION_TIME') : 0 }});</script>
						<div class="positive">{{ game.datezone("d.m H:i:s", list['BuildEndTime']) }}</div>
					{% else %}
						<a href="{{ url('buildings/index/listid/'~list['ListID']~'/cmd/remove/planet/'~list['PlanetID']~'/') }}">Удалить</a>
					{% endif %}
				</td>
			</tr>
		</table>
	{% endfor %}
	<div class="content">
		<div class="row" id="building">
			{% set i = 0 %}
			{% for build in parse['BuildingsList'] %}
			{% set i = i + 1 %}
			<div class="col-md-6 col-xs-12" id="object_{{ build['i'] }}">
				<div class="viewport buildings {% if build['access'] is false %}shadow{% endif %}">
					{% if build['access'] is false %}
						<div class="notAvailable tooltip" data-content="Требования:<br>{{ replace('"', "'", getTechTree(build['i'], this.user, this.planet)) }}" onclick="showWindow('{{ _text('xnova', 'tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}', 600, 500)"><span>недоступно</span></div>
					{% endif %}

					<div class="img">
						<a href="javascript:;" onclick="showWindow('{{ _text('xnova', 'tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}', 600)">
							<img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ build['i'] }}.gif" align="top" alt="" class="tooltip img-responsive" data-content='<center>{{ _text('xnova', 'descriptions', build['i']) }}</center>' data-tooltip-width="150">
						</a>

						<div class="overContent">
							{{ build['price'] }}
						</div>
					</div>
					<div class="title">
						<a href="{{ url('info/'~build['i']~'/') }}">{{ _text('xnova', 'tech', build['i']) }}</a>
					</div>
					<div class="actions">
						Уровень: <span class="{{ build['count'] > 0 ? 'positive' : 'negative' }}">{{ pretty_number(build['count']) }}</span><br>
						{% if build['access'] %}
							Время: {{ pretty_time(build['time']) }}<br>
							{{ build['add'] }}
							<div class="startBuild">{{ build['click'] }}</div>
						{% endif %}
					</div>
				</div>
			</div>
			{% endfor %}
		</div>
	</div>
</div>