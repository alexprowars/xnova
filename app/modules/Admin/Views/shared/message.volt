<div class="alert alert-success" role="alert">
	<h4 class="alert-heading">{{ title }}</h4>
	<p>{{ text }}</p>
</div>

{% if time and destination %}
	<script type="text/javascript">
		setTimeout(function(){location.href = '{{ destination }}';}, {{ time * 1000 }});
	</script>
{% endif %}