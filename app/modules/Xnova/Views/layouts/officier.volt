<div class="block">
	<div class="title">
		{{ parse['off_points'] }} {{ parse['alv_points'] }}
	</div>
	<div class="content container-fluid officiers">
		<div class="row">
			<div class="hidden-xs-down col-sm-2 text-xs-center">
				<img src="{{ url.getBaseUri() }}assets/images/officiers/bigcredits.jpg" alt="">
			</div>
			<div class="col-xs-12 col-sm-7 text-xs-left">
				Кредиты (<a href="{{ url('credits/') }}"><span class="positive">Получить</span></a>)<br><br>Инженеры берут за свою работу только межгалактичесие кредиты. В зависимости от суммы контракта работают на вас в течении всего времени найма.
				<table class="powers">
					<tr>
						<td><img src="{{ url.getBaseUri() }}assets/images/officiers/smalcredts.gif"></td>
						<td>При помощи кредитов можно нанять инженеров</td>
					</tr>
				</table>
			</div>
			<div class="col-sm-3 hidden-xs-down text-xs-center">
				<a href="{{ url('credits/') }}" class="positive">Получить кредиты</a>
			</div>
		</div>
		{% for parse['list'] AS $list %}
			<div class="row">
				<div class="hidden-xs-down col-sm-2 text-xs-center">
					<img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.jpg" align="top" alt=""/>
				</div>
				<div class="col-xs-12 col-sm-7 text-xs-left">
					<font color="#ff8900">{{ list['off_tx_lvl'] }} ({{ list['off_lvl'] }})</font>
					<br><br>
					{{ list['off_desc'] }}
					<table class="powers">
						<tr>
							<td rowspan="<?=(count($list['off_powr']) + 1) ?>"><img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.gif"></td>
						</tr>
						{% for list['off_powr'] as $power %}
							<tr><td class="up">{{ power }}</td></tr>
						{% endfor %}
					</table>
				</div>
				<div class="clearfix hidden-sm-up"><div class="separator"></div></div>
				<div class="col-xs-6 hidden-sm-up text-xs-center">
					<img src="{{ url.getBaseUri() }}assets/images/officiers/{{ list['off_id'] }}.jpg" align="top" alt=""/>
				</div>
				<div class="col-xs-6 col-sm-3 text-xs-center">
					<form method="POST" action="{{ url('officier/') }}">{{ list['off_link'] }}</form>
				</div>
			</div>
		{% endfor %}
	</div>
</div>