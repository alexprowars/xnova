<style>.image {
	max-width: 556px !important
}</style>
{% if isPopup is false %}
<div class="block">
	<div class="title">Информация об игроке</div>
	<div class="content container-fluid">
{% endif %}
			<div class="table">
				<div class="row">
					<div class="col-xs-4 text-xs-center">
						<img src="{{ parse['avatar'] }}" alt="{{ parse['username'] }}" width="100%">
						{% if parse['ingame'] %}
							<a href="javascript:;" onclick="showWindow('{{ parse['username'] }}: отправить сообщение', '{{ url('messages/write/'~parse['id']~'/') }}', 680)" title="Отправить сообщение"><span class='sprite skin_m'></span></a>&nbsp;
							<a href="{{ url('buddy/new/'~parse['id']~'/') }}" title="Добавить в друзья"><span class='sprite skin_b'></span></a>
						{% endif %}
					</div>
					<div class="col-xs-6">
						<div class="table">
							<div class="row">
								<div class="col-xs-4 text-xs-left p-a-0">Логин:</div>
								<div class="col-xs-8 p-a-0">{{ parse['username'] }}</div>
							</div>
							<div class="row">
								<div class="col-xs-4 text-xs-left p-a-0">Планета:</div>
								<div class="col-xs-8 p-a-0">
									<a href="{{ url('galaxy/'~parse['galaxy']~'/'~parse['system']~'/') }}" style="font-weight:normal">{{ parse['userplanet'] }} [{{ parse['galaxy'] }}:{{ parse['system'] }}:{{ parse['planet'] }}]</a>
								</div>
							</div>
							{% if parse['ally_name'] %}
								<div class="row">
									<div class="col-xs-4 text-xs-left p-a-0">Альянс:</div>
									<div class="col-xs-8 p-a-0">{{ parse['ally_name'] }}</div>
								</div>
							{% endif %}
							<div class="row">
								<div class="col-xs-4 text-xs-left p-a-0">Пол:</div>
								<div class="col-xs-8 p-a-0">{{ parse['sex'] }}</div>
							</div>
						</div>
						{% if parse['race'] != 0 %}
							<br><img src="{{ url.getBaseUri() }}assets/images/skin/race{{ parse['race'] }}.gif" alt="">
						{% endif %}
					</div>
					<div class="col-xs-2">
						<img src="{{ url.getBaseUri() }}assets/images/ranks/m{{ parse['m'] }}.png" alt="Промышленная отрасль" title="Промышленная отрасль"><br>
						<img src="{{ url.getBaseUri() }}assets/images/ranks/f{{ parse['f'] }}.png" alt="Военная отрасль" title="Военная отрасль">
					</div>
				</div>
			</div>
			<div class="table">
				<div class="row">
					<div class="c col-xs-12">Статистика игры</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">&nbsp;</div>
					<div class="c col-xs-4">Очки</div>
					<div class="c col-xs-4">Место</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Постройки</div>
					<div class="th col-xs-4">{{ parse['build_points'] }}</div>
					<div class="th col-xs-4">{{ parse['build_rank'] }}</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Иследования</div>
					<div class="th col-xs-4">{{ parse['tech_points'] }}</div>
					<div class="th col-xs-4">{{ parse['tech_rank'] }}</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Флот</div>
					<div class="th col-xs-4">{{ parse['fleet_points'] }}</div>
					<div class="th col-xs-4">{{ parse['fleet_rank'] }}</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Оборона</div>
					<div class="th col-xs-4">{{ parse['defs_points'] }}</div>
					<div class="th col-xs-4">{{ parse['defs_rank'] }}</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Всего</div>
					<div class="th col-xs-4">{{ parse['total_points'] }}</div>
					<div class="th col-xs-4">{{ parse['total_rank'] }}</div>
				</div>
			</div>
			<div class="separator"></div>
			<div class="table">
				<div class="row">
					<div class="c col-xs-12">Статистика боёв</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">&nbsp;</div>
					<div class="c col-xs-4">Сумма</div>
					<div class="c col-xs-4">Процент</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Победы</div>
					<div class="th col-xs-4"><b>{{ parse['wons'] }}</b></div>
					<div class="th col-xs-4">{{ parse['siegprozent'] }} %</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Поражения</div>
					<div class="th col-xs-4"><b>{{ parse['loos'] }}</b></div>
					<div class="th col-xs-4">{{ parse['loosprozent'] }} %</div>
				</div>
				<div class="row">
					<div class="c col-xs-4">Всего вылетов</div>
					<div class="th col-xs-4"><b>{{ parse['total'] }}</b></div>
					<div class="th col-xs-4">{{ parse['totalprozent'] }} %</div>
				</div>
			</div>
{% if isPopup is false %}
		{% if parse['about'] != '' %}
			<div class="row">
				<div class="b col-xs-12">
					<span id="m100500"></span>
					<script type="text/javascript">
						TextParser.addText('{{ preg_replace("/(\r\n)/u", "<br>", parse['about']|stripslashes) }}', 'm100500');
					</script>
				</div>
			</div>
		{% endif %}
	</div>
</div>
{% endif %}