<table class="table">
	<tr>
		<td class="c" colspan="6">Список друзей</td>
	</tr>
	<tr>
		<th colspan="6"><a href="{{ url('buddy/requests/') }}">Запросы</a></th>
	</tr>
	<tr>
		<th colspan="6"><a href="{{ url('buddy/requests/my/') }}">Мои запросы</a></th>
	</tr>
	<tr>
		<td class="c">&nbsp;</td>
		<td class="c">Имя</td>
		<td class="c">Альянс</td>
		<td class="c">Координаты</td>
		<td class="c">Позиция</td>
		<td class="c">&nbsp;</td>
	</tr>

	{% if (count($parse['list']) %}
		{% for parse['list'] AS $id => $list %}
			<tr>
				<th width="20"><?=($id + 1) ?></th>
				<th><a href="{{ url('messages/write/'~list['userid']~'') }}">{{ list['username'] }}</a></th>
				<th>{{ list['ally'] }}</th>
				<th><a href="{{ url('galaxy/'~list['g']~'/'~list['s']~'/') }}">{{ list['g'] }}:{{ list['s'] }}:{{ list['p'] }}</a></th>
				<th>{{ list['online'] }}</th>
				<th>
					<a href="{{ url('buddy/delete/'~list['id']~'/') }}">Удалить</a>
				</th>
			</tr>
		{% endfor %}
	{% else %}
		<tr>
			<th colspan="6">Нет друзей</th>
		</tr>
	{% endif %}
</table>

{% if (isset($okFriends) and count($okFriends) %}
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="4">Ваши друзья в Одноклассниках</td>
		</tr>
		<tr>
			{% for okFriends AS $ii => $data %}
				{% if ii > 0 and $ii%5 == 0 %}</tr><tr>{% endif %}
				<th width="20%">
					<div class="separator"></div>
					<table width="100%">
						<tr>
						  <td style="text-align:center;">
							<img src="{{ okArray[$data['ok_uid']]['pic128x128'] }}" style="max-width:96px">
						  </td>
						</tr>
						<tr>
						  <td style="text-align:center;">
							  {{ okArray[$data['ok_uid']]['name'] }}
							  {% if okArray[$data['ok_uid']]['name'] != $data['username'] %}<br>[{{ data['username'] }}]{% endif %}
							  <a href="javascript:;" onclick="showWindow('{{ data['username'] }}: отправить сообщение', '{{ url('messages/write/'~data['id']~'/') }}', 680)" data-link="Y" title="Сообщение"><span class='sprite skin_m'></span></a>
						  </td>
						</tr>
						<tr>
							<td>
								{{ data['planet_name'] }}<br>
								<a href="{{ url('galaxy/'~data['galaxy']~'/'~data['system']~'/') }}">[{{ data['galaxy'] }}:{{ data['system'] }}:{{ data['planet'] }}]</a>
							</td>
					  </tr>
					</table>
					<div class="separator"></div>
				</th>
			{% endfor %}
		</tr>
	</table>
{% endif %}