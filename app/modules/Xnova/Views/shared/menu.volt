<ul class="menu hidden-xs-down">
	{% for id, menu in _text('xnova', 'main_menu') if menu[2] <= adminlevel %}
		<li><a id="link_{{ id }}" {% if menu[3] is defined %}data-link="Y"{% endif %} href="{{ url(menu[1]) }}" {{ controller == id ? 'class="check"' : '' }}>{{ menu[0] }}</a></li>
	{% endfor %}
</ul>