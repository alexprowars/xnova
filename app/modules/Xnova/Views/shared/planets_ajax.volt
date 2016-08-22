{% if planet is defined and planet['list']|length %}
	<script type="text/javascript">
		$('.planetList .list').html('{% for i, item in planet['list'] %}<div class="planet type_{{ item['planet_type'] }} {{ planet['current'] == item['id'] ? 'current' : '' }}"><a href="javascript:;" onclick="changePlanet({{ item['id'] }})" title="{{ item['name'] }}"><img src="{{ url.getBaseUri() }}assets/images/planeten/small/s_{{ item['image'] }}.jpg" height="40" width="40" alt="{{ item['name'] }}"></a><span class="hidden-md-up">{{ planetLink(item) }}</span><div class="hidden-sm-down">{{ item['name'] }}<br>{{ planetLink(item) }}</div><div class="clear"></div></div>{% endfor %}');
	</script>
{% endif %}