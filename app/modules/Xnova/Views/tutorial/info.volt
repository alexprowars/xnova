<table class="table tutorial">
	<tr>
		<td class="k">
			<h3>Задание {{ parse['info']['TITLE'] }}</h3>
		</td>
	</tr>
	<tr>
		<td class="k text-xs-left">
			<div class="row">
				<div class="col-xs-4 text-xs-center">
					<img src="{{ url.getBaseUri() }}assets/images/tutorial/{{ stage }}.jpg" class="pic">
				</div>
				<div class="col-xs-8">
					<div class="description">
						{{ parse['info']['DESCRIPTION'] }}
					</div>
					<h3>Задачи:</h3>
					<ul>
						{% for task in parse['task'] %}
							<li>
								<span>{{ task[0] }}</span>
								<span><img src="{{ url.getBaseUri() }}assets/images/{{ task[1] ? 'check' : 'none' }}.gif" height="11" width="12"></span>
							</li>
						{% endfor %}
					</ul>
					<div style="color:orange;">
						Награда: <?=implode(', ', parse['rewd']) ?>
					</div>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="k">
			{% if errors is false %}
				<input type="button" class="end" onclick="load('{{ url.getBaseUri() }}tutorial/{{ stage }}/continue/1/')" value="Закончить">
			{% endif %}
			<div class="solution">
				{{ parse['info']['SOLUTION'] }}
			</div>
		</td>
	</tr>
</table>