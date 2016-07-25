<form action="{{ url('search/') }}" method="post">
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
</form>
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
							<a href="javascript:;" onclick="showWindow('{{ result['username'] }}: отправить сообщение', '{{ url('messages/write/'~result['id']~'/') }}', 680)" title="Написать сообщение"><span class='sprite skin_m'></span></a>
							<a href="{{ url('buddy/new/'~result['id']~'/') }}" title="Предложение подружиться"><span class='sprite skin_b'></span></a>
						</th>
						<th>{% if result['race'] != 0 %}<img src="{{ url.getBaseUri() }}assets/images/skin/race{{ result['race'] }}.gif" width="16" height="16">{% else %}&nbsp;{% endif %}
						</th>
						<th>{{ result['ally_name'] }}</th>
						<th>{{ result['planet_name'] }}</th>
						<th><a href="{{ url('galaxy/'~result['g']~'/'~result['s']~'/') }}">{{ result['g'] }}:{{ result['s'] }}:{{ result['p'] }}</a></th>
						<th><a href="{{ url('stat/players/range/'~result['total_rank']~'/') }}">{{ result['total_rank'] }}</a></th>
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
						<th><a href="{{ url('alliance/info/'~result['id']~'/') }}">{{ result['tag'] }}</a></th>
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