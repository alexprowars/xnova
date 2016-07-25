<table class="table">
	<tr>
		<td class="c" colspan="3">Текущие задания</td>
	</tr>
	{% for parse['list'] AS $quest %}
		<tr>
			<th width="30">{{ quest['ID'] }}</th>
			<th width="30"><img src="{{ url.getBaseUri() }}assets/images/<?=($quest['FINISH'] ? 'check' : 'none') ?>.gif" height="11" width="12"></th>
			<th class="text-xs-left">
				{% if quest['AVAILABLE'] %}
					<a href="{{ url('tutorial/'~quest['ID']~'/') }}"><span class="positive">{{ quest['TITLE'] }}</span></a>
				{% else %}
					<span class="positive">{{ quest['TITLE'] }}</span>
				{% endif %}
				{% if (!$quest['AVAILABLE'] and isset($quest['REQUIRED']) and count($quest['REQUIRED']) %}
					<br><br>Требования:
					{% for quest['REQUIRED'] AS $key => $req %}
						<br>
						{% if key == 'QUEST' %}
							<span class="<?=((!isset($parse['quests'][$req]) or (isset($parse['quests'][$req]) and $parse['quests'][$req]['finish'] == 0)) ? 'negative' : 'positive') ?>">Выполнение задания №{{ req }}</span>
						{% elseif ($key == 'LEVEL_MINIER' %}
							<span class="<?=(user.lvl_minier < $req ? 'negative' : 'positive') ?>">Промышленный уровень {{ req }}</span>
						{% elseif ($key == 'LEVEL_RAID' %}
							<span class="<?=(user.lvl_raid < $req ? 'negative' : 'positive') ?>">Военный уровень {{ req }}</span>
						{% endif %}
					{% endfor %}
				{% endif %}
			</th>
		</tr>
	{% endfor %}
</table>