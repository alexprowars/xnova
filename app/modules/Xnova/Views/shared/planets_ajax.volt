{% if planet['list']|length %}
	<script type="text/javascript">
		$('.planetList .list').html('{% for i, item in planet['list'] %}<div class="planet type_{{ item['planet_type'] }} {{ planet['current'] == item['id'] ? 'current' : '' }}"><a href="javascript:;" onclick="changePlanet({{ item['id'] }})" title="{{ item['name'] }}"><img src="{{ url.getBaseUri() }}assets/images/planeten/small/s_{{ item['image'] }}.jpg" height="40" width="40" alt="{{ item['name'] }}"></a><div>{{ item['name'] }}<br>{{ planetLink(item) }}</div><div class="clear"></div></div>{% endfor %}');
	</script>
{% endif %}