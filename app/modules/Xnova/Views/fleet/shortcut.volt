<table class="table">
	<tr>
		<td colspan="2" class="c">Ссылки (<router-link yo="{{ url('fleet/shortcut/add/new/') }}">Добавить</router-link>)</td>
	</tr>
	<tr>
		{% for i, link in links %}
			{% if i%2 == 0 %}</tr><tr>{% endif %}
			<th width="50%">
				<router-link yo="{{ url('fleet/shortcut/view/'~i~'/') }}">{{ link['name'] }} [{{ link['galaxy'] }}:{{ link['system'] }}:{{ link['planet'] }}] {{ link['type'] }}</router-link>
			</th>
		{% endfor %}
		{% if links|length%2 == 1 %}
			<th>&nbsp;</th>
		{% endif %}
		{% if links|length == 0 %}
			<th colspan="2">Список ссылок пуст</th>
		{% endif %}
	</tr>
	<tr>
		<td colspan="2" class="c"><router-link to="{{ url('fleet/') }}">Назад</router-link></td>
	</tr>
</table>
