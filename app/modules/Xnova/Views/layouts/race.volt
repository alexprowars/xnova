<div class="text-xs-center">
	<div class="table raceSelect">
		{% if race == 0 %}
			<div class="row">
				<div class="col-xs-12 c big">Выбор фракции</div>
			</div>
		{% endif %}
		<div class="row">
			<div class="col-xs-6 k big">
				<a href='javascript:;' onclick="showWindow('Конфедерация', '{{ url('info/701/') }}', 700, 500)">Конфедерация</a>
			</div>
			<div class="col-xs-6 k big">
				<a href='javascript:;' onclick="showWindow('Бионики', '{{ url('info/702/') }}', 700, 500)">Бионики</a>
			</div>
		</div>
		<div class="row">
			<div class="th col-xs-6 text-xs-left">
				<div style="text-align:center">
					<div class="separator"></div>
					<img src="{{ url.getBaseUri() }}assets/images/skin/race1.gif">
				</div>
				<br>
				<font color="#adff2f">Особенности расы:</font>
				<br>&nbsp;&nbsp;&nbsp;<font color="#84CFEF">+15% к добыче металла
					<br>&nbsp;&nbsp;&nbsp;+10% к скорости постройки кораблей
					<br>&nbsp;&nbsp;&nbsp;+15% к энергии спутников
					<br>&nbsp;&nbsp;&nbsp;-10% к стоимости улучшения кораблей
					<br>&nbsp;&nbsp;&nbsp;Уникальный корабль:
					<font color="#adff2f"><a href='javascript:;' onclick="showWindow('Конфедерация', '{{ url('info/220/') }}', 700, 500)">Корвет</a></font> (манёвренный и скоростной корабль)</font>
				<br><br>

				{% if race == 0 %}
					<div style="text-align:center"><a href="{{ url('race/index/sel/1/') }}"><input type="button" value="Выбрать"></a></div>
				{% endif %}<br>
			</div>
			<div class="th col-xs-6 text-xs-left">
				<div style="text-align:center">
					<div class="separator"></div>
					<img src="{{ url.getBaseUri() }}assets/images/skin/race2.gif">
				</div>
				<br>
				<font color="#adff2f">Особенности расы:</font>
				<br>&nbsp;&nbsp;&nbsp;<font color="#84CFEF">+15% к добыче дейтерия
					<br>&nbsp;&nbsp;&nbsp;-10% к стоимости постройки кораблей
					<br>&nbsp;&nbsp;&nbsp;+20% к вместимости хранилищ
					<br>&nbsp;&nbsp;&nbsp;+5% к энергии от солнечных батарей
					<br>&nbsp;&nbsp;&nbsp;Уникальный корабль:
					<font color="#adff2f"><a href='javascript:;' onclick="showWindow('Перехватчик', '{{ url('info/221/') }}', 700, 500)">Перехватчик</a></font> (скоростной легкий корабль)</font>
				<br><br>

				{% if race == 0 %}
					<div style="text-align:center"><a href="{{ url('race/index/sel/2/') }}"><input type="button" value="Выбрать"></a></div>
				{% endif %}<br>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 k big"><a href='javascript:;' onclick="showWindow('Сайлоны', '{{ url('info/703/') }}', 700, 500)">Сайлоны</a></div>
			<div class="col-xs-6 k big"><a href='javascript:;' onclick="showWindow('Древние', '{{ url('info/704/') }}', 700, 500)">Древние</a></div>
		</div>
		<div class="row">
			<div class="th col-xs-6 text-xs-left">
				<div style="text-align:center">
					<div class="separator"></div>
					<img src="{{ url.getBaseUri() }}assets/images/skin/race3.gif">
				</div>
				<br>
				<font color="#adff2f">Особенности расы:</font>
				<br>&nbsp;&nbsp;&nbsp;<font color="#84CFEF">+5% к добыче всех ресурсов
					<br>&nbsp;&nbsp;&nbsp;-5% к стоимости обороны
					<br>&nbsp;&nbsp;&nbsp;+10% к скорости постройки зданий
					<br>&nbsp;&nbsp;&nbsp;-5% к стоимости постройки зданий
					<br>&nbsp;&nbsp;&nbsp;Уникальный корабль:
					<font color="#adff2f"><a href='javascript:;' onclick="showWindow('Дредноут', '{{ url('info/222/') }}', 700, 500)">Дредноут</a></font> (тяжелый боевой корабль)</font>
				<br><br>

				{% if race == 0 %}
					<div style="text-align:center"><a href="{{ url('race/index/sel/3/') }}"><input type="button" value="Выбрать"></a></div>
				{% endif %}<br>
			</div>
			<div class="th col-xs-6 text-xs-left">
				<div style="text-align:center">
					<div class="separator"></div>
					<img src="{{ url.getBaseUri() }}assets/images/skin/race4.gif">
				</div>
				<br>
				<font color="#adff2f">Особенности расы:</font>
				<br>&nbsp;&nbsp;&nbsp;<font color="#84CFEF">+15% к добыче кристаллов
					<br>&nbsp;&nbsp;&nbsp;+10% к скорости полёта кораблей
					<br>&nbsp;&nbsp;&nbsp;+5% энергии от электростанций
					<br>&nbsp;&nbsp;&nbsp;-10% к стоимости исследований
					<br>&nbsp;&nbsp;&nbsp;Уникальный корабль:
					<font color="#adff2f"><a href='javascript:;' onclick="showWindow('Корсар', '{{ url('info/223/') }}', 700, 500)">Корсар</a></font> (быстрый пиратский корабль)</font>
				<br><br>

				{% if race == 0 %}
					<div style="text-align:center"><a href="{{ url('race/index/sel/4/') }}"><input type="button" value="Выбрать"></a></div>
				{% endif %}<br>
			</div>
		</div>
		{% if race != 0 %}
			<div class="row">
				<div class="col-xs-12 k big">
					{% if free_race_change > 0 %}
						Бесплатная смена фракции ({{ free_race_change }} попыток осталось):
					{% else %}
						Сменить фракцию за 100 кредитов:
					{% endif %}
				</div>
			</div>
			<div class="row">
				<div class="th col-sx-12">
					На планетах не должно идти строительство, исследования, летать флот и весь флот фракции подлежит демонтировке (без возврата ресурсов).<br><br>
					{% if isChangeAvailable %}
						<form action="{{ url('race/change/') }}" method="POST">
							<select name="race" title="">
								<option value="0">выбрать...</option>
								<option value="1">Конфедерация</option>
								<option value="2">Бионики</option>
								<option value="3">Сайлоны</option>
								<option value="4">Древние</option>
							</select>
							<br><br>
							<input type="submit" value="Сменить фракцию">
						</form>
					{% endif %}
				</div>
			</div>
		{% endif %}
	</div>
</div>

{% if race == 0 and !$isPopup %}
	<script type="text/javascript">
		$(document).ready(function()
		{
			showWindow('Информация', '{{ url('content/welcome/') }}');
		});
	</script>
{% endif %}