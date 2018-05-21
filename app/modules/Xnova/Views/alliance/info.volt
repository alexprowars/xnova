<table class="table" style="table-layout: fixed;">
	<tr>
		<td class="c" colspan="2">Информация об альянсе</td>
	</tr>
	{% if parse['image'] != "" %}
		<tr><th colspan="2"><img src="{{ parse['image'] }}" style="max-width:100%"></th></tr>
	{% endif %}
	<tr>
		<th>{{ _text('xnova', 'Tag') }}</th>
		<th>{{ parse['tag'] }}</th>
	</tr>
	<tr>
		<th>{{ _text('xnova', 'Name') }}</th>
		<th>{{ parse['name'] }}</th>
	</tr>
	<tr>
		<th>{{ _text('xnova', 'Members') }}</th>
		<th>{{ parse['member_scount'] }}</th>
	</tr>
	{% if parse['description'] != '' %}
		<tr>
			<td class="b" colspan="2" height="100" style="padding:3px;">
				<text-viewer text="{{ parse['description'] }}"></text-viewer>
			</td>
		</tr>
	{% endif %}
	{% if parse['web'] != '' %}
		<tr><th>Сайт альянса:</th><th><a href="{{ parse['web'] }}" target="_blank">{{ parse['web'] }}</a></th></tr>
	{% endif %}

	{% if userId is defined and userId != 0 and parse['request'] %}
		<tr>
			<th colspan="2">
				<a href="{{ url('alliance/apply/allyid/'~parse['id']~'/') }}" class="button">Вступить в альянс</a>
			</th>
		</tr>
	{% endif %}
</table>