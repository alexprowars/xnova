<table class="table">
	{% if (!$isPopup %}
		<tr>
			<td class="c" colspan="2">{{ parse['name'] }}</td>
		</tr>
		{% endif %}
		<tr>
			<th colspan="2" class="p-a-0">
				<table class="margin5">
					<tr>
						<td valign="top"><img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ parse['image'] }}.gif" class="info" align="top" border="0" height="120" width="120"></td>
						<td valign="top" class="text-xs-left">{{ parse['description'] }}</td>
					</tr>
				</table>
			</th>
		</tr>
	<tr>
		<th width="50%">Броня</th>
		<th>{{ parse['hull_pt'] }}</th>
	</tr>
	<tr>
	{% if parse['shield_pt'] > 0 %}
		<th>Мощность щита</th>
		<th>{{ parse['shield_pt'] }}</th>
</tr><tr>
{% endif %}
		<th>Оценка атаки</th>
		<th>{{ parse['attack_pt'] }}</th>
	</tr>
	{% if (isset($parse['gun']) and $parse['gun'] %}
	<tr>
		<th>Тип оружия</th>
		<th>{{ parse['gun'] }}</th>
	</tr>
		{% endif %}
	{% if (isset($parse['armour']) and $parse['armour'] %}
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
</table>
{% if (isset($parse['speedBattle']) and count($parse['speedBattle']) > 0 %}
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c text-xs-left">Скорострел</td>
			<td class="c positive">Поражает флот</td>
			<td class="c negative">Теряет флот</td>
		</tr>
		{% for parse['speedBattle'] AS $fId => $battle %}
			<tr>
				<th class="text-xs-left"><a href="{{ url('info/'~fId.'/') }}">{{ _text('tech', fId) }}</a></th>
				<th class="positive">
					{% if (isset($battle['TO']) %}
						{{ battle['TO'] }}
					{% else %}
						< 1
					{% endif %}
				</th>
				<th class="negative">
					{% if (isset($battle['FROM']) %}
						{{ battle['FROM'] }}
					{% else %}
						< 1
					{% endif %}
				</th>
			</tr>
		{% endfor %}
	</table>
{% endif %}