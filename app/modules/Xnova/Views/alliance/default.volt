<table class="table">
	<tr>
		<td class="c" colspan="2">Альянсы</td>
	</tr>
	<tr>
		<th><a href="{{ url('alliance/make/') }}">Создать альянс</a></th>
		<th><a href="{{ url('alliance/search/') }}">Поиск альянса</a></th>
	</tr>
</table>
{% if (count($parse['list']) > 0 %}
<br>
<table class="table">
	<tr>
		<td class="c" colspan="2">Ваши заявки</td>
	</tr>
	{% for parse['list'] AS $list %}
	<tr>
		<th width="70%">{{ list[2] }} [{{ list[1] }}]</th>
		<th>{{ game.datezone("d.m.Y H:i", $list[3]) }}</th>
	</tr>
	<tr>
		<th colspan="2">
			<form action="{{ url('alliance/') }}" method="POST"><input type="hidden" name="r_id" value="{{ list[0] }}"><input type="submit" name="bcancel" value="Убрать заявку"></form>
		</th>
	</tr>
	{% endfor %}
</table>
{% endif %}
{% if (count($parse['allys']) %}
	<br>
	<table class="table">
		<tr>
			<td class="c" width="30">Место</td>
			<td class="c">Альянс</td>
			<td class="c">Игроки</td>
			<td class="c">Очки</td>
		</tr>
		<? $i = 0; foreach ($parse['allys'] AS $list): $i++; ?>
		<tr>
			<th>{{ i }}</th>
			<th><a href="{{ url('alliance/info/'~list['id']~'/') }}">{{ list['name'] }} [{{ list['tag'] }}]</a></th>
			<th>{{ list['members'] }}</th>
			<th>{{ list['total_points'] }}</th>
		</tr>
		{% endfor %}
	</table>
{% endif %}