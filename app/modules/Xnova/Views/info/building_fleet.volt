<br>
<table width="600">
	<tbody>
	<tr>
		<td class="c" colspan="2">{{ parse['name'] }}</td>
	</tr>
	<tr>
		<th colspan="2">
			<table>
				<tbody>
				<tr>
					<td valign="top"><img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ parse['image'] }}.gif" class="info" align="top" border="0" height="120" width="120"></td>
					<td valign="top">{{ parse['description'] }}</td>
				</tr>
				</tbody>
			</table>
		</th>
	</tr>
	<tr>
		<th width="50%">Структура</th>
		<th>{{ parse['hull_pt'] }}</th>
	</tr>
	{% if parse['image'] != 212 %}
		<tr>
			<th>Оценка атаки</th>
			<th>{{ parse['attack_pt'] }}</th>
		</tr>
		<tr>
			<th>Грузоподъёмность</th>
			<th>{{ parse['capacity_pt'] }}</th>
		</tr>
		<tr>
			<th>Начальная скорость</th>
			<th>{{ parse['base_speed'] }} {{ parse['upd_speed'] }}</th>
		</tr>
		<tr>
			<th>Потребление топлива (дейтерий)</th>
			<th>{{ parse['base_conso'] }}</th>
		</tr>
		<tr>
			<th>Тип двигателя</th>
			<th>{{ parse['base_engine'] }}</th>
		</tr>
		<tr>
			<th>Тип оружия</th>
			<th>{{ parse['gun'] }}</th>
		</tr>
		<tr>
			<th>Тип брони</th>
			<th>{{ parse['armour'] }}</th>
		</tr>
	{% endif %}
	<tr>
		<td class="c" colspan="2">Затраты на производство</td>
	</tr>
	<tr>
		<th>Металл</th>
		<th>{{ parse['met'] }}</th>
	</tr>
	<tr>
		<th>Кристалл</th>
		<th>{{ parse['cry'] }}</th>
	</tr>
	<tr>
		<th>Дейтерий</th>
		<th>{{ parse['deu'] }}</th>
	</tr>
	</tbody>
</table>
{% if parse['image'] != 212 %}
	<br>
	<table width="600">
		<tr>
			<td width="50%">
				<table width="100%">
					<tr>
						<td class="c">Тип корабля</td>
						<td class="c">Сопротивление брони</td>
					</tr>
					{% for list in parse['soprot'] %}
						<tr>
							<th>{{ list[0] }}</th>
							<th width="35%" class="positive">{{ list[1] }}%</th>
						</tr>
					{% endfor %}
				</table>
			</td>
			<td width="50%">
				<table width="100%">
					<tr>
						<td class="c">Тип корабля</td>
						<td class="c">Сопротивление атаке</td>
					</tr>
					{% for list in parse['soprot_2'] %}
						<tr>
							<th>{{ list[0] }}</th>
							<th width="35%" class="positive">{{ list[1] }}%</th>
						</tr>
					{% endfor %}
				</table>
			</td>
		</tr>
	</table>
	<br><a href="http://forum.xnova.su/viewtopic.php?f=1&t=3137" target="_blank">Что за цифры?</a><br>
{% endif %}