{% if planet is defined and planet['list']|length %}
	<a href="#" class="planet-toggle hidden-sm-up"><span>
			<span class="first"></span>
			<span class="second"></span>
			<span class="third"></span>
		</span>
	</a>
		<div class="planet-sidebar planetList">
			<div class="list ">
				{% for i, item in planet['list'] %}
					<div class="planet type_{{ item['planet_type'] }} {{ planet['current'] == item['id'] ? 'current' : '' }}">
						<a href="javascript:" onclick="changePlanet({{ item['id'] }})" title="{{ item['name'] }}">
							<img src="{{ url.getBaseUri() }}assets/images/planeten/small/s_{{ item['image'] }}.jpg" height="40" width="40" alt="{{ item['name'] }}">
						</a>
						<span class="hidden-md-up">{{ planetLink(item) }}</span>
						<div class="hidden-sm-down">
							{{ item['name'] }}
							<br>
							{{ planetLink(item) }}
						</div>
						<div class="clear"></div>
					</div>
				{% endfor %}
				<div class="clearfix"></div>
			</div>
		</div>
	{% if ajaxNavigation != 0 %}
		<script type="text/javascript">
			$(document).ready(function()
			{
				$('.planetList .list').on('mouseup', 'a', function()
				{
					$('.planetList .planet').removeClass('current');
					$(this).parents('.planet').addClass('current');
				});
			});
		</script>
	{% endif %}
{% endif %}