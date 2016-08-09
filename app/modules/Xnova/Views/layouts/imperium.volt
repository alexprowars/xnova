<div class="table-responsive" id="imperium">
	<table class="table">
		<tr valign="left">
			<td class="c" colspan="{{ parse['mount'] }}">{{ _text('xnova', 'imperium_vision') }}</td>
		</tr>
		<tr>
			<th colspan="2">&nbsp;</th>
			{{ parse['file_images'] }}
			<th width="100">Сумма</th>
		</tr>
		<tr>
			<th colspan="2">{{ _text('xnova', 'name') }}</th>
			{{ parse['file_names'] }}
			<th>&nbsp;</th>
		</tr>
		<tr>
			<th colspan="2">{{ _text('xnova', 'coordinates') }}</th>
			{{ parse['file_coordinates'] }}
			<th>&nbsp;</th>
		</tr>
		<tr>
			<th colspan="2">{{ _text('xnova', 'fields') }}</th>
			{{ parse['file_fields'] }}
			<th>{{ parse['file_fields_c'] }} / {{ parse['file_fields_t'] }}</th>
		</tr>

		<tr>
			<td class="c" colspan="{{ parse['mount'] }}" align="left">{{ _text('xnova', 'resources') }}</td>
		</tr>
		<tr>
			<th rowspan="5">на планете</th>
			<th>{{ _text('xnova', 'metal') }}</th>
			{% for item in parse['file_metal'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_metal_t'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'crystal') }}</th>
			{% for item in parse['file_crystal'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_crystal_t'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'deuterium') }}</th>
			{% for item in parse['file_deuterium'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_deuterium_t'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'energy') }}</th>
			{{ parse['file_energy'] }}
			<th>{{ parse['file_energy_t'] }}</th>
		</tr>
		<tr>
			<th>Заряд</th>
			{{ parse['file_zar'] }}
			<th>&nbsp;</th>
		</tr>


		<tr>
			<th rowspan="3">в час</th>
			<th>{{ _text('xnova', 'metal') }}</th>
			{% for item in parse['file_metal_ph'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_metal_ph_t'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'crystal') }}</th>
			{% for item in parse['file_crystal_ph'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_crystal_ph_t'] }}</th>
		</tr>
		<tr>
			<th>{{ _text('xnova', 'deuterium') }}</th>
			{% for item in parse['file_deuterium_ph'] %}
				<th>{{ pretty_number(item) }}</th>
			{% endfor %}
			<th>{{ parse['file_deuterium_ph_t'] }}</th>
		</tr>


		<tr>
			<th rowspan="6">Производство</th>
			<th>Металл</th>
			{% for item in parse['file_metal_p'] %}
				<th><span class="positive">{{ pretty_number(item) }}</span>%</th>
			{% endfor %}
			<th rowspan="6">&nbsp;</th>
		</tr>
		<tr>
			<th>Кристаллы</th>
			{% for item in parse['file_crystal_p'] %}
				<th><span class="positive">{{ pretty_number(item) }}</span>%</th>
			{% endfor %}
		</tr>
		<tr>
			<th>Дейтерий</th>
			{% for item in parse['file_deuterium_p'] %}
				<th><span class="positive">{{ pretty_number(item) }}</span>%</th>
			{% endfor %}
		</tr>
		<tr>
			<th>Солн. ст.</th>{{ parse['file_solar_p'] }}</tr>
		<tr>
			<th>Терм. ст.</th>{{ parse['file_fusion_p'] }}</tr>
		<tr>
			<th>Спутники</th>{{ parse['file_solar2_p'] }}</tr>
		<tr>
			<th colspan="{{ parse['mount'] - 1 }}">Кредиты</th>
			<th><span class="neutral">{{ parse['file_kredits'] }}</span></th>
		</tr>
		<tr>
			<td class="c" colspan="{{ parse['mount'] }}" align="left">{{ _text('xnova', 'buildings') }}</td>
		</tr>
		{{ parse['building_row'] }}
		<tr>
			<td class="c" colspan="{{ parse['mount'] }}" align="left">{{ _text('xnova', 'ships') }}</td>
		</tr>
		{{ parse['fleet_row'] }}
		<tr>
			<td class="c" colspan="{{ parse['mount'] }}" align="left">{{ _text('xnova', 'defense') }}</td>
		</tr>
		{{ parse['defense_row'] }}
		<tr>
			<td class="c" colspan="{{ parse['mount'] }}" align="left">{{ _text('xnova', 'investigation') }}</td>
		</tr>
		{{ parse['technology_row'] }}
	</table>
</div>