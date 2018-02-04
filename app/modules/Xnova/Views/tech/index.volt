<tabs class="page-techtree">
	{% for groups in parse %}
		<tab name="{{ groups['name'] }}" class="container-fluid">
			{% for list in groups['items'] %}
			<div class="row">
				<div class="col-sm-5 col-6 title">
					<a href="{{ url('info/'~list['info']~'/') }}">{{ list['name'] }}</a>

					{% if list['required'] != '' %}
						<div class="float-right d-none d-sm-block"><a href="{{ url('tech/'~list['info']~'/') }}">[i]</a></div>
					{% endif %}
				</div><div class="col-sm-7 col-6">
					{{ list['required'] }}
				</div>
			</div>
			{% endfor %}
		</tab>
	{% endfor %}
</tabs>