<table class="table">
	<tr>
		<td colspan="2" class="c">Ссылки (<a href="{{ url('fleet/shortcut/add/new/') }}">Добавить</a>)</td>
	</tr>
	<tr>
		{% for i, link in links %}
			{% if i%2 == 0 %}</tr><tr>{% endif %}
			<th width="50%">
				<a href="{{ url('fleet/shortcut/view/'~i~'/') }}">{{ link['name'] }} [{{ link['galaxy'] }}:{{ link['system'] }}:{{ link['planet'] }}] {{ link['type'] }}</a>
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
		<td colspan="2" class="c"><a href="{{ url('fleet/') }}">Назад</a></td>
	</tr>
</table>
