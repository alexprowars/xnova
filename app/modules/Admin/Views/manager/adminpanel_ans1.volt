<div class="row">
	<div class="col-md-6">
		<div class="card">
			<div class="card-title card-title-bold">{{ _text('admin', 'adm_panel_mnu') }}</div>
			<table class="table table-striped">
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
			</table>
		</div>

		{% if parse['list_tech']|length > 0 %}
			<div class="card">
				<div class="card-title card-title-bold">{{ _text('admin', 'adm_technos') }}</div>
				<table class="table table-striped">
					{% for tech, level in parse['list_tech'] if level > 0 %}
						<tr>
							<td>{{ _text('xnova', 'tech', tech) }}</td>
							<td>{{ level }}</td>
						</tr>
					{% endfor %}
				</table>
			</div>
		{% endif %}

		{% if parse['list_attacks']|length > 0 %}
			<div class="card">
				<div class="card-title card-title-bold">Логи атак</div>
				<table class="table table-striped">
					{% for item in parse['list_attacks'] %}
						<tr>
							<td width="40%">{{ item['date'] }}</td>
							<td>S:{{ item['start'] }}</td>
							<td width="30%">E:{{ item['end'] }}</td>
						</tr>
						<tr>
							<td colspan="3">
								<a href="{{ item['url'] }}" target="_blank">{{ item['fleet'] }}</a>
							</td>
						</tr>
					{% endfor %}
				</table>
			</div>
		{% endif %}
	</div>
	<div class="col-md-6">
		{% if parse['list_ip']|length > 0 %}
			<div class="card">
				<div class="card-title card-title-bold">Смены IP</div>
				<table class="table table-striped">
					{% for item in parse['list_ip'] %}
						<tr>
							<td>{{ item['ip'] }}</td>
							<td>{{ item['date'] }}</td>
						</tr>
					{% endfor %}
				</table>
			</div>
		{% endif %}

		{% if parse['list_credits']|length > 0 %}
			<div class="card">
				<div class="card-title card-title-bold">Кредитная история</div>
				<table class="table table-striped">
					{% for item in parse['list_credits'] %}
						<tr>
							<td width="40%">{{ item['date'] }}</td>
							<td>{{ item['credits'] }}</td>
							<td width="40%">{{ item['type'] }}</td>
						</tr>
					{% endfor %}
				</table>
			</div>
		{% endif %}
	</div>
</div>

{% if parse['list_mult']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Пересечения по IP</div>
		<table class="table table-striped">
			{% for item in parse['list_mult'] %}
				<tr>
					<td width="40%">{{ item['date'] }}</td>
					<td>{{ item['ip'] }}</td>
					<td width="30%">
						<a href="{{ url("admin/manager/data/username/"~item['user_id']~"/send/") }}" target="_blank">{{ item['user_name'] }}</a>
					</td>
				</tr>
			{% endfor %}
		</table>
	</div>
{% endif %}

{% if parse['list_ld']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Записи в личном деле</div>
		<table class="table table-striped">
			{% for item in parse['list_ld'] %}
				<tr>
					<td width="25%">{{ item['date'] }}</td>
					<td width="20%">
						<a href="{{ url("players/"~item['user_id']~"/") }}" target="_blank">{{ item['user_id'] }}</a>
					</td>
					<td>{{ item['text'] }}</td>
				</tr>
			{% endfor %}
		</table>
	</div>
{% endif %}

{% if parse['planet_list']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Список планет</div>
		<div class="card-body">
			<div class="accordion">
				{% for planet in parse['planet_list'] %}
					<div class="card">
						<h5 class="card-title">
							<a data-toggle="collapse" href="#planet_{{ planet['id'] }}">
								{% if planet['planet_type'] == 1 %}
									{{ _text('admin', 'adm_planet') }}
								{% elseif planet['planet_type'] == 3 %}
									{{ _text('admin', 'adm_moon') }}
								{% elseif planet['planet_type'] == 5 %}
									{{ _text('admin', 'adm_base') }}
								{% endif %}

								[{{ planet['galaxy'] }}:{{ planet['system'] }}:{{ planet['planet'] }}] {{ planet['name'] }}, #{{ planet['id'] }}
							</a>
						</h5>
						<div id="planet_{{ planet['id'] }}" class="collapse">
							<table class="table table-striped">
								{% for field, value in planet %}
									<tr>
										<td>{{ (array_search(field, parse['planet_fields']) ? _text('tech', array_search(field, parse['planet_fields'])) : field) }}</td>
										<td><b>{{ value }}</b></td>
									</tr>
								{% endfor %}
							</table>
						</div>
					</div>
				{% endfor %}
			</div>
		</div>
	</div>
{% endif %}

{% if parse['list_transfer']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Передачи ресурсов</div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Дата</th>
					<th>Игрок</th>
					<th>Откуда</th>
					<th>Куда</th>
					<th>Метал</th>
					<th>Кристал</th>
					<th>Дейтерий</th>
				</tr>
			</thead>
			{% for history in parse['list_transfer'] %}
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
{% endif %}

{% if parse['list_transfer_income']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Получение ресурсов</div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Дата</th>
					<th>Игрок</th>
					<th>Откуда</th>
					<th>Куда</th>
					<th>Метал</th>
					<th>Кристал</th>
					<th>Дейтерий</th>
				</tr>
			</thead>
			{% for history in parse['list_transfer_income'] %}
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
{% endif %}

{% if parse['list_history']|length > 0 %}
	<div class="card">
		<div class="card-title card-title-bold">Активность</div>
		<table class="table table-striped">
			<thead>
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
			</thead>
			{% for history in parse['list_history'] %}
				<tr>
					<td>{{ date("d.m.Y H:i:s", history['time']) }}</td>
					<td>{{ history['planet'] }}</td>
					<td>{{ parse['history_actions'][history['operation']] }}</td>
					<td>
						{{ (history['build_id'] > 0 ? _text('xnova', 'tech', history['build_id']) : '') }}
						{{ (history['tech_id'] > 0 ? _text('xnova', 'tech', history['tech_id']) : '') }}
					</td>
					<td>{{ (history['count'] ? history['count'] : history['level']) }}</td>
					<td>{{ history['from_metal'] }} -> {{ history['to_metal'] }} ({{ (history['to_metal'] - history['from_metal']) }})</td>
					<td>{{ history['from_crystal'] }} -> {{ history['to_crystal'] }} ({{ (history['to_crystal'] - history['from_crystal']) }})</td>
					<td>{{ history['from_deuterium'] }} -> {{ history['to_deuterium'] }} ({{ (history['to_deuterium'] - history['from_deuterium']) }})</td>
				</tr>
			{% endfor %}
		</table>
	</div>
{% endif %}