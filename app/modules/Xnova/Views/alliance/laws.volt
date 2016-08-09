<form action="{{ url('alliance/admin/edit/rights/') }}" method="POST">
	<table class="table">
		<tr>
			<td class="c" colspan="13">{{ _text('xnova', 'Configure_laws') }}</td>
		</tr>
		<tr>
			<th colspan="2">Имя ранга</th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r1.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r2.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r3.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r4.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r5.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r6.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r7.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r8.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r9.png width="16"></th>
			<th><img src={{ url.getBaseUri() }}assets/images/alliance/r10.gif width="16"></th>
		</tr>
		{% if parse['list']|length > 0 %}
			{% for r in parse['list'] %}
				<tr>
					<th>{{ r['delete'] }}<input type="hidden" name="id[]" value="{{ r['a'] }}"></th>
					<th>&nbsp;{{ r['r0'] }}&nbsp;</th>
					<th>{{ r['r1'] }}</th>
					<th>{{ r['r2'] }}</th>
					<th>{{ r['r3'] }}</th>
					<th>{{ r['r4'] }}</th>
					<th>{{ r['r5'] }}</th>
					<th>{{ r['r6'] }}</th>
					<th>{{ r['r7'] }}</th>
					<th>{{ r['r8'] }}</th>
					<th>{{ r['r9'] }}</th>
					<th>{{ r['r10'] }}</th>
				</tr>
			{% endfor %}
			<tr>
				<th colspan="13"><input type="submit" value="Сохранить"></th>
			</tr>
		{% else %}
			<tr>
				<th colspan="13" align="center">нет рангов</th>
			<tr>
		{% endif %}
	</table>
</form>
<div class="separator"></div>
<form action="{{ url('alliance/admin/edit/rights/add/name/') }}" method="POST">
	<table class="table">
		<tr>
			<td class="c" colspan="2">{{ _text('xnova', 'Range_make') }}</td>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'Range_name') }}</th>
			<th><input type="text" name="newrangname" size=20 maxlength=30 title=""></th>
		</tr>
		<tr>
			<th colspan="2"><input type="submit" value="{{ _text('xnova', 'Make') }}"></th>
		</tr>
	</table>
</form>
<div class="separator"></div>
<table class="table">
	<tr>
		<td class="c" colspan="2">{{ _text('xnova', 'Law_leyends') }}</td>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r1.png></th>
		<th>{{ _text('xnova', 'Alliance_dissolve') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r2.png></th>
		<th>{{ _text('xnova', 'Expel_users') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r3.png></th>
		<th>{{ _text('xnova', 'See_the_requests') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r4.png></th>
		<th>{{ _text('xnova', 'See_the_list_members') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r5.png></th>
		<th>{{ _text('xnova', 'Check_the_requests') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r6.png></th>
		<th>{{ _text('xnova', 'Alliance_admin') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r7.png></th>
		<th>{{ _text('xnova', 'See_the_online_list_member') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r8.png></th>
		<th>{{ _text('xnova', 'Make_a_circular_message') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r9.png></th>
		<th>{{ _text('xnova', 'Left_hand_text') }}</th>
	</tr>
	<tr>
		<th><img src={{ url.getBaseUri() }}assets/images/alliance/r10.gif></th>
		<th>Дипломатия</th>
	</tr>
	<tr>
		<td class="c" colspan="2"><a href="{{ url('alliance/admin/edit/ally/') }}">{{ _text('xnova', 'Return_to_overview') }}</a></td>
	</tr>
</table>