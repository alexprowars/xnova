<div class="note note-danger">
	<h4 class="block">{{ title }}</h4>
	<p>
		{{ text }}
	</p>
</div>

{% if time and destination %}
	<script type="text/javascript">
		setTimeout(function(){location.href = '{{ destination }}';}, {{ time * 1000 }});
	</script>
{% endif %}