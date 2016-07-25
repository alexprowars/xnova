<form action="{{ url('fleet/stagethree/') }}" method="post">
	<input type="hidden" name="thisresource1"  value="{{ parse['thisresource1'] }}" />
	<input type="hidden" name="thisresource2"  value="{{ parse['thisresource2'] }}" />
	<input type="hidden" name="thisresource3"  value="{{ parse['thisresource3'] }}" />
	<input type="hidden" name="consumption"    value="{{ parse['consumption'] }}" />
	<input type="hidden" name="stayConsumption" value="{{ parse['stayConsumption'] }}" />
	<input type="hidden" name="dist"           value="{{ parse['dist'] }}" />
	<input type="hidden" name="acs"            value="{{ parse['acs'] }}" />
	<input type="hidden" name="thisgalaxy"     value="{{ parse['thisgalaxy'] }}" />
	<input type="hidden" name="thissystem"     value="{{ parse['thissystem'] }}" />
	<input type="hidden" name="thisplanet"     value="{{ parse['thisplanet'] }}" />
	<input type="hidden" name="galaxy"         value="{{ parse['galaxy'] }}" />
	<input type="hidden" name="system"         value="{{ parse['system'] }}" />
	<input type="hidden" name="planet"         value="{{ parse['planet'] }}" />
	<input type="hidden" name="planettype"     value="{{ parse['planettype'] }}" />
	<input type="hidden" name="speed"    	   value="{{ parse['speed'] }}" />
	<input type="hidden" name="usedfleet"      value="{{ parse['usedfleet'] }}" />
	<input type="hidden" name="crc"            value="{{ parse['crc'] }}" />
	<input type="hidden" name="maxepedition"   value="{{ parse['maxepedition'] }}" />
	<input type="hidden" name="curepedition"   value="{{ parse['curepedition'] }}" />
	{% for ship in parse['ships'] %}
		<input type="hidden" name="ship{{ ship['id'] }}" value="{{ ship['count'] }}" />
		<input type="hidden" name="stay{{ ship['id'] }}" value="{{ ship['stay'] }}" />
		<input type="hidden" name="consumption{{ ship['id'] }}" value="{{ ship['consumption'] }}" />
		<input type="hidden" name="speed{{ ship['id'] }}" value="{{ ship['speed'] }}" />
		<input type="hidden" name="capacity{{ ship['id'] }}" value="{{ ship['capacity'] }}" />
	{% endfor %}
	<div class="table">
		<div class="row">
			<div class="c col-xs-12">{{ parse['galaxy'] }}:{{ parse['system'] }}:{{ parse['planet'] }} - {{ _text('type_planet', parse['planettype']) }}</div>
		</div>
		<div class="row">
			<div class="th col-xs-6">
				<table class="table">
					<tr>
						<td class="c" colspan="2">{{ _text('fl_mission') }}</td>
					</tr>
					{% for a, b in parse['missions'] %}
						<tr>
							<th style="text-align: left !important">
								<input id="m_{{ a }}" type="radio" name="mission" value="{{ a }}"{{ parse['missions_selected'] == a ? 'checked' : '' }}>
								<label for="m_{{ a }}">{{ b }}</label>
								{% if a == 15 %}
									<center><font color="red">{{ _text('fl_expe_warning') }}</font></center>
								{% endif %}
							</th>
						</tr>
					{% endfor %}
					{% if parse['missions']|length == 0 %}
						<tr>
							<th><font color="red">{{ _text('fl_bad_mission') }}</font></th>
						</tr>
					{% endif %}
					<tr>
						<th>Время прилёта: <span id='end_time'>00:00:00</span></th>
					</tr>
				</table>
			</div>
			<div class="th col-xs-6">
				<table class="table">
					<tr>
						<td colspan="3" class="c">{{ _text('fl_ressources') }}</td>
					</tr>
					<tr>
						<th>{{ _text('Metal') }}</th>
						<th><a href="javascript:maxResource('1');">{{ _text('fl_selmax') }}</a></th>
						<th><input name="resource1" alt="{{ _text('Metal') }}" size="10" onchange="calculateTransportCapacity();" type="text" title=""></th>
					</tr>
					<tr>
						<th>{{ _text('Crystal') }}</th>
						<th><a href="javascript:maxResource('2');">{{ _text('fl_selmax') }}</a></th>
						<th><input name="resource2" alt="{{ _text('Crystal') }}" size="10" onchange="calculateTransportCapacity();" type="text" title=""></th>
					</tr>
					<tr>
						<th>{{ _text('Deuterium') }}</th>
						<th><a href="javascript:maxResource('3');">{{ _text('fl_selmax') }}</a></th>
						<th><input name="resource3" alt="{{ _text('Deuterium') }}" size="10" onchange="calculateTransportCapacity();" type="text" title=""></th>
					</tr>
					<tr>
						<th>{{ _text('fl_space_left') }}</th>
						<th colspan="2"><div id="remainingresources">-</div></th>
					</tr>
					<tr>
						<th colspan="3"><a href="javascript:maxResources()">{{ _text('fl_allressources') }}</a></th>
					</tr>
					<tr>
						<th colspan="3">&nbsp;</th>
					</tr>
					{% if parse['expedition_hours'] is defined %}
						<tr class="mission m_15">
							<td class="c" colspan="3">Время экспедиции</td>
						</tr>
						<tr class="mission m_15">
							<th colspan="3">
								<select name="expeditiontime" title="">
									{% for i in 1..parse['expedition_hours'] %}
										<option value="{{ i }}">{{ i }} ч.</option>
									{% endfor %}
								</select>
							</th>
						</tr>
					{% endif %}
					{% if parse['missions'][5] is defined %}
						<tr class="mission m_5">
							<td class="c" colspan="3">Оставаться часов на орбите</td>
						</tr>
						<tr class="mission m_5">
							<th colspan="3">
								<select name="holdingtime" title="">
									<option value="0">0</option>
									<option value="1" selected>1</option>
									<option value="2">2</option>
									<option value="4">4</option>
									<option value="8">8</option>
									<option value="16">16</option>
									<option value="32">32</option>
								</select>
								<div id="stayRes"></div>
							</th>
						</tr>
					{% endif %}
					{% if parse['missions'][1] is defined %}
						<tr class="mission m_1">
							<td class="c" colspan="3">Кол-во раундов боя</td>
						</tr>
						<tr class="mission m_1">
							<th colspan="3">
								<select name="raunds" title="">
									<option value="6" selected>6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
							</th>
						</tr>
					{% endif %}
				</table>
			</div>
		</div>
		{% if parse['missions']|length %}
			<div class="row">
				<div class="th col-xs-12"><input accesskey="z" value="{{ _text('fl_continue') }}" type="submit"></div>
			</div>
		{% endif %}
	</div>
</form>
<script type="text/javascript">

	var mission = 0;

	$(document).ready(function()
	{
		mission = $('input[name=mission]:checked').val();
		durationTime = duration() * 1000;

		durationTimer();

		$('.mission').hide();

		if ($('.mission.m_'+mission+'').length)
			$('.mission.m_'+mission+'').show();

		calculateTransportCapacity();

		$("select[name=holdingtime]").on('change', function()
		{
			var obj = $(this).val();

			if (obj <= 0)
				$('#stayRes').hide();
			else
			{
				var res = parseInt($('input[name=stayConsumption]').val()) * obj;

				$('#stayRes').html('<br>Потребуется <span class="positive">'+number_format(res, 0, ',', '.')+'</span> дейтерия').show();
			}

			calculateTransportCapacity();
		});

		$("input[name=mission]").on('change', function()
		{
			$('.mission').hide();

			mission = $(this).val();

			if ($('.mission.m_'+mission+'').length)
				$('.mission.m_'+mission+'').show();

			calculateTransportCapacity();
		});
	});
</script>