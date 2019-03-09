<table class="table">
	<tr>
		<td class="c" colspan="10">Список членов альянса (количество: {{ parse['i'] }})</td>
	</tr>
	<tr>
		<th>№</th>
		<th><router-link to="{{ url('alliance/'~(parse['admin'] ? 'admin/edit/members' : 'members')~'/sort1/1/sort2/'~parse['s']~'/') }}">Ник</router-link></th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th><router-link to="{{ url('alliance/'~(parse['admin'] ? 'admin/edit/members' : 'members')~'/sort1/2/sort2/'~parse['s']~'/') }}">Ранг</router-link></th>
		<th><router-link to="{{ url('alliance/'~(parse['admin'] ? 'admin/edit=members' : 'members')~'/sort1/3/sort2/'~parse['s']~'/') }}">Очки</router-link></th>
		<th>Координаты</th>
		<th><router-link to="{{ url('alliance/'~(parse['admin'] ? 'admin/edit/members' : 'members')~'/sort1/4/sort2/'~parse['s']~'/') }}">Дата вступления</router-link></th>
		{% if parse['status'] %}<th><router-link to="{{ url('alliance/'~(parse['admin'] ? 'admin/edit/members' : 'members')~'/sort1/5/sort2/'~parse['s']~'/') }}">Активность</router-link></th>{% endif %}
		{% if parse['admin'] %}<th>Управление</th>{% endif %}
	</tr>
	{% for m in parse['memberslist'] %}
		{% if m['Rank_for'] is not defined or parse['admin'] is false %}
			<tr>
				<th>{{ m['i'] }}</th>
				<th>{{ m['username'] }}</th>
				<th><popup-link to="/messages/write/{{ m['id'] }}/" title="{{ m['username'] }}: отправить сообщение" :width="680"><span class='sprite skin_m'></span></popup-link></th>
				<th><img src="{{ url.getBaseUri() }}images/skin/race{{ m['race'] }}.gif" width="16" height="16"></th>
				<th>{{ m['range'] }}</th>
				<th>{{ m['points'] }}</th>
				<th><router-link to="{{ url('galaxy/'~m['galaxy']~'/'~m['system']~'/') }}">{{ m['galaxy'] }}:{{ m['system'] }}:{{ m['planet'] }}</router-link></th>
				<th>{{ m['time'] }}</th>
				{% if parse['status'] %}<th>{{ m['onlinetime'] }}</th>{% endif %}
				{% if parse['admin'] %}<th><a href="{{ url('alliance/admin/edit/members/kick/'~m['id']~'/') }}" onclick="return confirm('Вы действительно хотите исключить данного игрока из альянса?');"><img src="{{ url.getBaseUri() }}images/abort.gif"></a>&nbsp;<router-link to="{{ url('alliance/admin/edit/members/rank/'~m['id']~'/') }}"><img src="{{ url.getBaseUri() }}assets/images/key.gif"></router-link></th>{% endif %}
			</tr>
		{% else %}
			<tr>
				<td colspan="10">
					<router-form action="{{ url('alliance/admin/edit/members/id/'~m['id']~'/') }}">
						<table class="table">
							<tr>
								<th colspan="7">{{ m['Rank_for'] }}</th>
								<th><select name="newrang" title="">{{ m['options'] }}</select></th>
								<th colspan="2"><input type="submit" value="Сохранить"></th>
							</tr>
						</table>
					</router-form>
				</td>
			</tr>
		{% endif %}
	{% endfor %}
	<tr>
		<td class="c" colspan="10"><router-link to="{{ url('alliance'~(parse['admin'] ? '/admin/edit/ally' : '')~'/') }}">{{ _text('xnova', 'Return_to_overview') }}</router-link></td>
	</tr>
</table>
