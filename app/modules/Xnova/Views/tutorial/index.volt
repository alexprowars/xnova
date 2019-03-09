<table class="table">
	<tr>
		<td class="c" colspan="3">Текущие задания</td>
	</tr>
	{% for quest in parse['list'] %}
		<tr>
			<th width="30">{{ quest['ID'] }}</th>
			<th width="30"><img src="{{ url.getBaseUri() }}images/{{ quest['FINISH'] ? 'check' : 'none' }}.gif" height="11" width="12"></th>
			<th class="text-left">
				{% if quest['AVAILABLE'] %}
					<router-link to="{{ url('tutorial/'~quest['ID']~'/') }}"><span class="positive">{{ quest['TITLE'] }}</span></router-link>
				{% else %}
					<span class="positive">{{ quest['TITLE'] }}</span>
				{% endif %}
				{% if quest['AVAILABLE'] is false and quest['REQUIRED'] is defined and quest['REQUIRED']|length %}
					<br><br>Требования:
					{% for key, req in quest['REQUIRED'] %}
						<br>
						{% if key == 'QUEST' %}
							<span class="{{ (parse['quests'][req] is not defined or (parse['quests'][req] is defined and parse['quests'][req]['finish'] == 0)) ? 'negative' : 'positive' }}">Выполнение задания №{{ req }}</span>
						{% elseif key == 'LEVEL_MINIER' %}
							<span class="{{ user.lvl_minier < req ? 'negative' : 'positive' }}">Промышленный уровень {{ req }}</span>
						{% elseif key == 'LEVEL_RAID' %}
							<span class="{{ user.lvl_raid < req ? 'negative' : 'positive' }}">Военный уровень {{ req }}</span>
						{% endif %}
					{% endfor %}
				{% endif %}
			</th>
		</tr>
	{% endfor %}
</table>