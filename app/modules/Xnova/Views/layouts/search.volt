<router-form action="{{ url('search/') }}">
	<table class="table">
		<tr>
			<td class="c">Поиск по игре</td>
		</tr>
		<tr>
			<th>
				<select name="type" title="">
					<option value="playername" {{ parse['type'] == "playername" ? "selected" : "" }}>Логин игрока</option>
					<option value="planetname" {{ parse['type'] == "planetname" ? "selected" : "" }}>Название планеты</option>
					<option value="allytag" {{ parse['type'] == "allytag" ? "selected" : "" }}>Аббревиатура альянса</option>
					<option value="allyname" {{ parse['type'] == "allyname" ? "selected" : "" }}>Название альянса</option>
				</select>
				&nbsp;&nbsp;
				<input type="text" name="searchtext" value="{{ parse['searchtext'] }}" title="">
				&nbsp;&nbsp;
				<input type="submit" value="Поиск">
			</th>
		</tr>
	</table>
</router-form>
<div class="separator"></div>
{% if parse['searchtext'] != '' %}
	{% if parse['type'] is defined and (parse['type'] == 'playername' or parse['type'] == 'planetname') %}
		<table class="table">
			<tr>
				<td class="c" width="120">Имя</td>
				<td class="c" width="40">&nbsp;</td>
				<td class="c" width="20">&nbsp;</td>
				<td class="c">Альянс</td>
				<td class="c">Планета</td>
				<td class="c" width="80">Координаты</td>
				<td class="c" width="40">Место</td>
			</tr>
			{% if parse['result']|length > 0 %}
				{% for result in parse['result'] %}
					<tr>
						<th>{{ result['username'] }}</th>
						<th nowrap>
							<popup-link to="/messages/write/{{ result['id'] }}/" title="{{ result['username'] }}: отправить сообщение" :width="680"><span class='sprite skin_m'></span></popup-link>
							<router-link to="{{ url('buddy/new/'~result['id']~'/') }}" title="Предложение подружиться"><span class='sprite skin_b'></span></router-link>
						</th>
						<th>{% if result['race'] != 0 %}<img src="{{ url.getBaseUri() }}assets/images/skin/race{{ result['race'] }}.gif" width="16" height="16">{% else %}&nbsp;{% endif %}
						</th>
						<th>{{ result['ally_name'] }}</th>
						<th>{{ result['planet_name'] }}</th>
						<th><router-link to="{{ url('galaxy/'~result['g']~'/'~result['s']~'/') }}">{{ result['g'] }}:{{ result['s'] }}:{{ result['p'] }}</router-link></th>
						<th><router-link to="{{ url('stat/players/range/'~result['total_rank']~'/') }}">{{ result['total_rank'] }}</router-link></th>
					</tr>
				{% endfor %}
			{% else %}
				<tr>
					<th colspan="7">Поиск не дал результатов</th>
				</tr>
			{% endif %}
		</table>
	{% else %}

		<table class="table">
			<tr>
				<td class="c">Аббревиатура</td>
				<td class="c">Имя</td>
				<td class="c">Члены</td>
				<td class="c">Очки</td>
			</tr>
			{% if parse['result']|length > 0 %}
				{% for result in parse['result'] %}
					<tr>
						<th><router-link to="{{ url('alliance/info/'~result['id']~'/') }}">{{ result['tag'] }}</router-link></th>
						<th>{{ result['name'] }}</th>
						<th>{{ result['members'] }}</th>
						<th>{{ result['total_points'] }}</th>
					</tr>
				{% endfor %}
			{% else %}
				<tr>
					<th colspan="6">Поиск не дал результатов</th>
				</tr>
			{% endif %}
		</table>

	{% endif %}

{% endif %}