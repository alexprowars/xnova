<div class="page-officiers block">
	<div class="title">
		{{ parse['off_points'] }} {{ parse['alv_points'] }}
	</div>
	<div class="content container-fluid">
		<div class="row">
			<div class="d-none d-sm-block col-sm-2 text-center">
				<img src="{{ url.getBaseUri() }}assets/images/officiers/bigcredits.jpg" alt="">
			</div>
			<div class="col-12 col-sm-7 text-left">
				Кредиты (<a href="{{ url('credits/') }}"><span class="positive">Получить</span></a>)<br><br>
				Инженеры берут за свою работу только межгалактичесие кредиты. В зависимости от суммы контракта работают на вас в течении всего времени найма.
				<table class="powers">
					<tr>
						<td class="padding-0"><img src="{{ url.getBaseUri() }}assets/images/officiers/smalcredts.gif"></td>
						<td>При помощи кредитов можно нанять инженеров</td>
					</tr>
				</table>
			</div>
			<div class="col-sm-3 d-none d-sm-block text-center">
				<a href="{{ url('credits/') }}" class="button positive">Получить кредиты</a>
			</div>
		</div>
		{% for list in parse['list'] %}
			<div class="row officiers-item">
				<div class="col-12 officiers-item-title">{{ list['off_tx_lvl'] }} ({{ list['off_lvl'] }})</div>
				<div class="d-none d-sm-block col-sm-2 text-center officiers-item-image">
					<img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.jpg" align="top" alt=""/>
				</div>
				<div class="col-12 col-sm-7 text-left officiers-item-description">
					{{ list['off_desc'] }}
					<table class="powers">
						<tr>
							<td rowspan="{{ list['off_powr']|length + 1 }}" valign="top" class="padding-0">
								<img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.gif">
							</td>
						</tr>
						{% for power in list['off_powr'] %}
							<tr>
								<td class="up">{{ power }}</td>
							</tr>
						{% endfor %}
					</table>
				</div>
				<div class="clearfix d-sm-none">
					<div class="separator"></div>
				</div>
				<div class="col-6 d-sm-none text-center officiers-item-image">
					<img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.jpg" align="top" alt=""/>
				</div>
				<div class="col-6 col-sm-3 text-center officiers-item-action">
					<form method="post" action="{{ url('officier/') }}">
						{{ list['off_link'] }}
					</form>
				</div>
			</div>
		{% endfor %}
	</div>
</div>