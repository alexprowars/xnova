<table class="table">
	{% for key, value in _SERVER %}
		<tr>
			<th class="text-left">{{ key }}</th>
			<td class="c text-left">{{ value }}</td>
		</tr>
	{% endfor %}
</table>