<table class="table">
	{% if title is defined and title != '' %}
		<tr>
			<td class="c error">{{ title }}</td>
		</tr>
	{% endif %}
	<tr>
		<th class="errormessage">{{ text }}</th>
	</tr>
</table>
{% if time and destination %}
	<script type="text/javascript">
	{% if request->isAjax() %}
		timeouts['message'] = setTimeout(function(){load('{{ destination }}')}, {{ time * 1000 }});
	{% else %}
		setTimeout(function(){location.href = '{{ destination }}';}, {{ time * 1000 }});
	{% endif %}
	</script>
{% endif %}