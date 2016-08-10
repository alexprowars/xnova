<div class="row">
	<div class="col-md-6">
		<table class="table table-striped table-hover table-advance">
			<tbody>
			<tr>
				<th colspan="2">{{ _text('admin', 'adm_panel_mnu') }}</th>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_id') }}</td>
				<td align="center">{{ parse['answer1'] }}</td>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_name') }}</td>
				<td align="center">{{ parse['answer2'] }}</td>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_ip') }}</td>
				<td align="center">{{ parse['answer3'] }}</td>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_mail') }}</td>
				<td align="center">{{ parse['answer4'] }}</td>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_acc') }}</td>
				<td align="center" class="negative">{{ parse['answer5'] }}</td>
			</tr>
			<tr>
				<td align="center">{{ _text('admin', 'adm_frm1_gen') }}</td>
				<td align="center">{{ parse['answer6'] }}</td>
			</tr>
			<tr>
				<td align="center">Дата регистрации</td>
				<td align="center">{{ parse['answer9'] }}</td>
			</tr>
			<tr>
				<td align="center">РО</td>
				<td align="center">{{ parse['answer7'] }}</td>
			</tr>
			</tbody>
		</table>
		{{ parse['adm_sub_form3'] }}
	</div>
	<div class="col-md-6">
		{{ parse['adm_sub_form4'] }}
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		{{ parse['adm_sub_form5'] }}
	</div>
</div>

{% if parse['planet_list']|length > 0 %}
	<div class="portlet box yellow">
		<div class="portlet-title">
			<div class="caption">
				Список планет
			</div>
		</div>
		<div class="portlet-body">
			<div class="panel-group accordion">
				{% for planet in parse['planet_list'] %}
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
							<a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" href="#planet_{{ planet['id'] }}">
								{% if planet['planet_type'] == 1 %}
									{{ _text('admin', 'adm_planet') }}
								{% elseif planet['planet_type'] == 3 %}
									{{ _text('admin', 'adm_moon') }}
								{% elseif planet['planet_type'] == 5 %}
									{{ _text('admin', 'adm_base') }}
								{% endif %}

								[{{ planet['galaxy'] }}:{{ planet['system'] }}:{{ planet['planet'] }}] {{ planet['name'] }}, #{{ planet['id'] }}
							</a>
							</h4>
						</div>
						<div id="planet_{{ planet['id'] }}" class="panel-collapse collapse">
							<div class="panel-body">
								<table class='table'>
									{% for field, value in planet %}
										<tr>
											<td>{{ (array_search(field, parse['planet_fields']) ? _text('tech', array_search(field, parse['planet_fields'])) : field) }}</td>
											<td><b>{{ value }}</b></td>
										</tr>
									{% endfor %}
								</table>
							</div>
						</div>
					</div>
				{% endfor %}
			</div>
		</div>
	</div>
{% endif %}
{% if parse['transfer_list']|length > 0 %}
	<div class="portlet box yellow">
		<div class="portlet-title">
			<div class="caption">
				Передачи ресурсов
			</div>
		</div>
		<div class="portlet-body">
			<table class='table'>
				<tr>
					<th>Дата</th>
					<th>Игрок</th>
					<th>Откуда</th>
					<th>Куда</th>
					<th>Метал</th>
					<th>Кристал</th>
					<th>Дейтерий</th>
				</tr>
				{% for history in parse['transfer_list'] %}
					<tr>
						<td>{{ date("d.m.Y H:i:s", history['time']) }}</td>
						<td>{{ history['target'] }}</td>
						<td>{{ history['start'] }}</td>
						<td>{{ history['end'] }}</td>
						<td>{{ number_format(history['metal'], 0, '.', ' ') }}</td>
						<td>{{ number_format(history['crystal'], 0, '.', ' ') }}</td>
						<td>{{ number_format(history['deuterium'], 0, '.', ' ') }}</td>
					</tr>
				{% endfor %}
			</table>
		</div>
	</div>
{% endif %}
{% if parse['transfer_list_income']|length > 0 %}
	<div class="portlet box yellow">
		<div class="portlet-title">
			<div class="caption">
				Получение ресурсов
			</div>
		</div>
		<div class="portlet-body">
			<table class='table'>
				<tr>
					<th>Дата</th>
					<th>Игрок</th>
					<th>Откуда</th>
					<th>Куда</th>
					<th>Метал</th>
					<th>Кристал</th>
					<th>Дейтерий</th>
				</tr>
				{% for history in parse['transfer_list_income'] %}
					<tr>
						<td>{{ date("d.m.Y H:i:s", history['time']) }}</td>
						<td>{{ history['target'] }}</td>
						<td>{{ history['start'] }}</td>
						<td>{{ history['end'] }}</td>
						<td>{{ number_format(history['metal'], 0, '.', ' ') }}</td>
						<td>{{ number_format(history['crystal'], 0, '.', ' ') }}</td>
						<td>{{ number_format(history['deuterium'], 0, '.', ' ') }}</td>
					</tr>
				{% endfor %}
			</table>
		</div>
	</div>
{% endif %}
{% if parse['history_list']|length > 0 %}
	<div class="portlet box yellow">
		<div class="portlet-title">
			<div class="caption">
				Активность
			</div>
		</div>
		<div class="portlet-body">
			<table class='table'>
				<tr>
					<th>Дата</th>
					<th>Планета</th>
					<th>Операция</th>
					<th>Постройка</th>
					<th>Ур/кол</th>
					<th>Метал</th>
					<th>Кристал</th>
					<th>Дейтерий</th>
				</tr>
				{% for history in parse['history_list'] %}
					<tr>
						<td>{{ date("d.m.Y H:i:s", history['time']) }}</td>
						<td>{{ history['planet'] }}</td>
						<td>{{ parse['history_actions'][history['operation']] }}</td>
						<td>{{ (history['build_id'] > 0 ? _text('xnova', 'tech', history['build_id']) : '') }}</td>
						<td>{{ (history['count'] ? history['count'] : history['level']) }}</td>
						<td>{{ history['from_metal'] }} -> {{ history['to_metal'] }} ({{ (history['to_metal'] - history['from_metal']) }})</td>
						<td>{{ history['from_crystal'] }} -> {{ history['to_crystal'] }} ({{ (history['to_crystal'] - history['from_crystal']) }})</td>
						<td>{{ history['from_deuterium'] }} -> {{ history['to_deuterium'] }} ({{ (history['to_deuterium'] - history['from_deuterium']) }})</td>
					</tr>
				{% endfor %}
			</table>
		</div>
	</div>
{% endif %}