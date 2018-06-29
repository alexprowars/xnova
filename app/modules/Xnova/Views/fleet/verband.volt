<table class="table">
	<tr>
		<td colspan="9" class="c">Флоты в совместной атаке</td>
	</tr>
	<tr>
		<th>ID</th>
		<th>Задание</th>
		<th>Кол-во</th>
		<th>Отправлен</th>
		<th>Прибытие (цель)</th>
		<th>Цель</th>
		<th>Прибытие (возврат)</th>
		<th>Прибудет через</th>
		<th>Планета старта</th>
	</tr>
	{% for i, item in parse['list'] %}
		<tr>
			<th>{{ i + 1 }}</th>
			<th>
				<a>{{ _text('xnova', 'type_mission', item.mission) }}</a>
				{% if (item.start_time + 1) == item.end_time %}
					<a>(F)</a>
				{% endif %}
			</th>
			<th>
				<a class="tooltip" data-content="{% for t, f in item.getShips() %}{{ _text('xnova', 'tech', t) }}: {{ f['count'] }}<br>{% endfor %}">
					{{ pretty_number(item.getTotalShips()) }}
				</a>
			</th>
			<th>{{ item.getStartAdressLink() }}</th>
			<th>{{ game.datezone("d.m H:i:s", item.start_time) }}</th>
			<th>{{ item.getTargetAdressLink() }}</th>
			<th>{{ game.datezone("d.m H:i:s", item.end_time) }}</th>
			<th>
				<div id="time_0" class="positive">{{ pretty_time(floor(item.end_time + 1 - time())) }}</div>
			</th>
			<th>{{ item.owner_name }}</th>
		</tr>
	{% endfor %}
	{% if parse['list']|length %}
		<tr><th colspan="9">-</th></tr>
	{% endif %}
</table>

{% if parse['group'] == 0 %}
	<div class="separator"></div>
	<router-form action="{{ url('fleet/verband/id/'~parse['fleetid']~'/') }}">
		<input type="hidden" name="action" value="add">
		<table class="table">
		<tr>
			<td class="c" colspan="2">Создание ассоциации флота</td>
		</tr>
		<tr>
			<th colspan="2">
				<input type="text" name="name" value="AKS{{ rand(100000, 999999999) }}" size=50 title="">
				<br />
				<input type="submit" value="Создать" />
			</th>
		</tr>
		</table>
	</router-form>
{% elseif parse['aks'] is defined and parse['fleetid'] == parse['aks']['fleet_id'] %}
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="2">Ассоциация флота {{ parse['aks']['name'] }}</td>
		</tr>
		<tr>
			<th colspan="2">
				<router-form action="{{ url('fleet/verband/id/'~parse['fleetid']~'/') }}">
					<input type="hidden" name="action" value="changename"/>
					<input type="text" name="name" value="{{ parse['aks']['name'] }}" size=50 title="">
					<br/>
					<input type="submit" value="Изменить"/>
				</router-form>
			</th>
		</tr>
		<tr>
			<th>
				<table class="table">
					<tr>
						<td class="c">Приглашенные участники</td>
						<td class="c">Пригласить участников</td>
					</tr>
					<tr>
						<th width="50%" valign="top">
							<select size="10" style="width:75%;" title="">
								{% if parse['users'] is defined and parse['users']|length %}
									{% for user in parse['users'] %}
										<option>{{ user }}</option>
									{% endfor %}
								{% else %}
									<option>нет участников</option>
								{% endif %}
							</select>
						</th>
						<th>
							<router-form action="{{ url('fleet/verband/id/'~parse['fleetid']~'/') }}">
								<input type="hidden" name="action" value="adduser">
								{% if parse['friends']|length or parse['alliance']|length %}
									<select name="user_id" size="10" style="width:75%;" title="">
										<option value="">-не выбрано-</option>
										{% if parse['friends']|length %}
											<optgroup label="Список друзей">
												{% for user in parse['friends'] %}
													<option value="{{ user['id'] }}">{{ user['username'] }}</option>
												{% endfor %}
											</optgroup>
										{% endif %}

										{% if parse['alliance']|length %}
											<optgroup label="Члены альянса">
												{% for user in parse['alliance'] %}
													<option value="{{ user['id'] }}">{{ user['username'] }}</option>
												{% endfor %}
											</optgroup>
										{% endif %}
									</select>
									<div class="separator"></div>
								{% endif %}
								<input type="text" name="user_name" size="40" placeholder="Введите игровой ник" />
								<br>
								<input type="submit" value="OK" />
							</router-form>
						</th>
					</tr>
				</table>
			</th>
		</tr>
	</table>
{% endif %}