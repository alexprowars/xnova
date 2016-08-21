<table class="table">
	<tr>
		<td class="c" colspan="7">Обнаружена следующая активность на планете:
		</td>
	</tr>
	{% if list|length == 0 %}
		<tr>
			<th>На этой планете нет движения флотов.</th>
		</tr>
	{% else %}
		{% for item in list %}
			{{ item }}
		{% endfor %}
	{% endif %}
</table>