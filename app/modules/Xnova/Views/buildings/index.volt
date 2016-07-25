<div class="block">
	<div class="title">
		Занято полей
		<span class="positive">{{ parse['planet_field_current'] }}</span> из <span class="negative">{{ parse['planet_field_max'] }}</span>
		<div class="pull-xs-right col-xs-12 col-sm-6 p-a-0">Осталось <span class="positive">{{ parse['field_libre'] }}</span> свободн<?=\Xnova\Helpers::morph($parse['field_libre'], 'neuter', 2) ?> пол<?=\Xnova\Helpers::morph($parse['field_libre'], 'neuter', 1) ?></div>
		<div class="clearfix"></div>
	</div>
	{% for list in parse['BuildList'] %}
		<table class="table" id="building">
			<tr>
				<td class="c" width="50%">
					{{ list['ListID'] }}: {{ list['ElementTitle'] }} {{ list['BuildLevel'] }}{% if list['BuildMode'] != 'build' %}. {{ _text('destroy') }}{% endif %}
				</td>
				<td class="k">
					{% if list['ListID'] == 1 %}
						<div id="blc" class="z"></div>
						<script type="text/javascript">BuildTimeout({{ list['BuildTime'] }}, {{ list['ListID'] }}, {{ list['PlanetID'] }}, <?=(isset($_SESSION['LAST_ACTION_TIME']) ? $_SESSION['LAST_ACTION_TIME'] : 0) ?>);</script>
						<div class="positive">{{ game.datezone("d.m H:i:s", $list['BuildEndTime']) }}</div>
					{% else %}
						<a href="{{ url('buildings/index/listid/'~list['ListID']~'/cmd/remove/planet/'~list['PlanetID']~'/') }}">Удалить</a>
					{% endif %}
				</td>
			</tr>
		</table>
	{% endfor %}
	<div class="content">
		<div id="tabs" class="ui-tabs ui-widget ui-widget-content">
			<div class="head hidden-xs-down">
				<ul class="ui-tabs-nav ui-widget-header">
					<li id="tab_area"><a href="#tabs-0">Планета</a></li>
					<li id="tab_list"><a href="#tabs-1">Постройки</a></li>
				</ul>
			</div>
			<div id="tabs-0" class="ui-tabs-panel ui-widget-content">
				<div class="buildings area {{ parse['planettype'] }}">
					{% for parse['BuildingsList'] AS $build): if (!$build['access']) continue; ?>
						<div data-id="{{ build['i'] }}" class="object i_{{ build['i'] }} <?=($build['count'] <= 0 ? 'empty' : '') ?> tooltip" data-content='<center>{{ _text('descriptions', build['i']) }}</center>' data-tooltip-width="150">
							<img src="{{ url.getBaseUri() }}assets/images/buildings/{{ build['i'] }}.png" alt="{{ _text('tech', build['i']) }}">
							<div class="name">{{ _text('tech', build['i']) }} <span>{{ pretty_number(build['count']) ?></span></div>
						</div>
					{% endfor %}
				</div>
			</div>
			<div id="tabs-1" class="ui-tabs-panel ui-widget-content" style="display: none">
				<div class="row" id="building">
					<? $i = 0; for ($parse['BuildingsList'] AS $build): $i++; ?>
					<div class="col-md-6 col-xs-12" id="object_{{ build['i'] }}">
						<div class="viewport buildings {% if (!$build['access'] %}shadow{% endif %}">
							{% if (!$build['access'] %}
								<div class="notAvailable tooltip" data-content="Требования:<br><?=str_replace('"', '\'', \Xnova\Building::getTechTree($build['i'], user, planet)) ?>" onclick="showWindow('{{ _text('tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}', 600, 500)"><span>недоступно</span></div>
							{% endif %}

							<div class="img">
								<a href="javascript:;" onclick="showWindow('{{ _text('tech', build['i']) }}', '{{ url('info/'~build['i']~'/') }}', 600)">
									<img src="{{ url.getBaseUri() }}assets/images/gebaeude/{{ build['i'] }}.gif" align="top" alt="" class="tooltip img-responsive" data-content='<center>{{ _text('descriptions', build['i']) }}</center>' data-tooltip-width="150">
								</a>

								<div class="overContent">
									{{ build['price'] }}
								</div>
							</div>
							<div class="title">
								<a href="{{ url('info/'~build['i']~'/') }}">{{ _text('tech', build['i']) }}</a>
							</div>
							<div class="actions">
								Уровень: <span class="<?=($build['count'] > 0 ? 'positive' : 'negative') ?>">{{ pretty_number(build['count']) ?></span><br>
								{% if build['access'] %}
									Время: <?=\Xnova\Helpers::pretty_time($build['time']); ?><br>
									{{ build['add'] }}
									<div class="startBuild">{{ build['click'] }}</div>
								{% endif %}
							</div>
						</div>
					</div>
					{% endfor %}
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		var tab = parseInt(localStorage.getItem("buildingAreaTab"));

		if (isNaN(tab))
			tab = 0;
		
		if (!$('#tab_area').is(':visible'))
			tab = 1;
		
		$( "#tabs" ).tabs({active: tab, activate: function( event, ui ) 
		{
			var t = $(ui.newPanel).attr('id').split('-');
			
			localStorage.setItem("buildingAreaTab", parseInt(t[1]));
		}});

		$('#building').on('click', '#blc', function()
		{
			$(this).remove();
		});

		$('.buildings.area .object').on('click', function()
		{
			var id = $(this).data('id');

			$('#windowDialog')
					.dialog("option", "title", 'Информация о постройке')
					.dialog("option", "height", 167)
					.html($('#object_'+id).html())
					.dialog("option", "position", {my: "center", at: "center", of: window})
					.dialog("open");
		});
	});
</script>