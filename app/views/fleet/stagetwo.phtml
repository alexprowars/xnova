<form action="?set=fleet&page=fleet_3" method="post">
	<input type="hidden" name="thisresource1"  value="<?=$parse['thisresource1'] ?>" />
	<input type="hidden" name="thisresource2"  value="<?=$parse['thisresource2'] ?>" />
	<input type="hidden" name="thisresource3"  value="<?=$parse['thisresource3'] ?>" />
	<input type="hidden" name="consumption"    value="<?=$parse['consumption'] ?>" />
	<input type="hidden" name="stayConsumption" value="<?=$parse['stayConsumption'] ?>" />
	<input type="hidden" name="dist"           value="<?=$parse['dist'] ?>" />
	<input type="hidden" name="acs"            value="<?=$parse['acs'] ?>" />
	<input type="hidden" name="thisgalaxy"     value="<?=$parse['thisgalaxy'] ?>" />
	<input type="hidden" name="thissystem"     value="<?=$parse['thissystem'] ?>" />
	<input type="hidden" name="thisplanet"     value="<?=$parse['thisplanet'] ?>" />
	<input type="hidden" name="galaxy"         value="<?=$parse['galaxy'] ?>" />
	<input type="hidden" name="system"         value="<?=$parse['system'] ?>" />
	<input type="hidden" name="planet"         value="<?=$parse['planet'] ?>" />
	<input type="hidden" name="planettype"     value="<?=$parse['planettype'] ?>" />
	<input type="hidden" name="speed"    	   value="<?=$parse['speed'] ?>" />
	<input type="hidden" name="usedfleet"      value="<?=$parse['usedfleet'] ?>" />
	<input type="hidden" name="crc"            value="<?=$parse['crc'] ?>" />
	<input type="hidden" name="maxepedition"   value="<?=$parse['maxepedition'] ?>" />
	<input type="hidden" name="curepedition"   value="<?=$parse['curepedition'] ?>" />
	<? foreach ($parse['ships'] as $ship): ?>
		<input type="hidden" name="ship<?=$ship['id'] ?>" value="<?=$ship['count'] ?>" />
		<input type="hidden" name="stay<?=$ship['id'] ?>" value="<?=$ship['stay'] ?>" />
		<input type="hidden" name="consumption<?=$ship['id'] ?>" value="<?=$ship['consumption'] ?>" />
		<input type="hidden" name="speed<?=$ship['id'] ?>" value="<?=$ship['speed'] ?>" />
		<input type="hidden" name="capacity<?=$ship['id'] ?>" value="<?=$ship['capacity'] ?>" />
	<? endforeach; ?>
	<table class='table'>
		<tr align="left">
			<td class="c" colspan="2"><?=$parse['galaxy'] ?>:<?=$parse['system'] ?>:<?=$parse['planet'] ?> - <?=_getText('type_planet', $parse['planettype']) ?></td>
		</tr>
		<tr align="left" valign="top">
			<th class="col-xs-6">
				<table class="table">
					<tr>
						<td class="c" colspan="2"><?=_getText('fl_mission') ?></td>
					</tr>
					<? foreach ($parse['missions'] AS $a => $b): ?>
						<tr>
							<th style="text-align: left !important">
								<input id="m_<?=$a ?>" type="radio" name="mission" value="<?=$a ?>"<?=($parse['missions_selected'] == $a ? 'checked' : '') ?>>
								<label for="m_<?=$a ?>"><?=$b ?></label>
								<? if ($a == 15): ?>
									<center><font color="red"><?=_getText('fl_expe_warning') ?></font></center>
								<? endif; ?>
							</th>
						</tr>
					<? endforeach; ?>
					<? if (!count($parse['missions'])): ?>
						<tr>
							<th><font color="red"><?=_getText('fl_bad_mission') ?></font></th>
						</tr>
					<? endif; ?>
					<tr>
						<th>Время прилёта: <span id='end_time'>00:00:00</span></th>
					</tr>
				</table>
			</th>
			<th class="col-xs-6">
				<table class="table">
					<tr>
						<td colspan="3" class="c"><?=_getText('fl_ressources') ?></td>
					</tr>
					<tr>
						<th><?=_getText('Metal') ?></th>
						<th><a href="javascript:maxResource('1');"><?=_getText('fl_selmax') ?></a></th>
						<th><input name="resource1" alt="<?=_getText('Metal') ?>" size="10" onchange="calculateTransportCapacity();" type="text"></th>
					</tr>
					<tr>
						<th><?=_getText('Crystal') ?></th>
						<th><a href="javascript:maxResource('2');"><?=_getText('fl_selmax') ?></a></th>
						<th><input name="resource2" alt="<?=_getText('Crystal') ?>" size="10" onchange="calculateTransportCapacity();" type="text"></th>
					</tr>
					<tr>
						<th><?=_getText('Deuterium') ?></th>
						<th><a href="javascript:maxResource('3');"><?=_getText('fl_selmax') ?></a></th>
						<th><input name="resource3" alt="<?=_getText('Deuterium') ?>" size="10" onchange="calculateTransportCapacity();" type="text"></th>
					</tr>
					<tr>
						<th><?=_getText('fl_space_left') ?></th>
						<th colspan="2"><div id="remainingresources">-</div></th>
					</tr>
					<tr>
						<th colspan="3"><a href="javascript:maxResources()"><?=_getText('fl_allressources') ?></a></th>
					</tr>
					<tr>
						<th colspan="3">&nbsp;</th>
					</tr>
					<? if (isset($parse['expedition_hours'])): ?>
						<tr class="mission m_15">
							<td class="c" colspan="3">Время экспедиции</td>
						</tr>
						<tr class="mission m_15">
							<th colspan="3">
								<select name="expeditiontime">
									<? for ($i = 1; $i <= $parse['expedition_hours']; $i++): ?>
										<option value="<?=$i ?>"><?=$i ?> ч.</option>
									<? endfor; ?>
								</select>
							</th>
						</tr>
					<? endif; ?>
					<? if (isset($parse['missions'][5])): ?>
						<tr class="mission m_5">
							<td class="c" colspan="3">Оставаться часов на орбите</td>
						</tr>
						<tr class="mission m_5">
							<th colspan="3">
								<select name="holdingtime" >
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
					<? endif; ?>
					<? if (isset($parse['missions'][1])): ?>
						<tr class="mission m_1">
							<td class="c" colspan="3">Кол-во раундов боя</td>
						</tr>
						<tr class="mission m_1">
							<th colspan="3">
								<select name="raunds">
									<option value="6" selected>6</option>
									<option value="7">7</option>
									<option value="8">8</option>
									<option value="9">9</option>
									<option value="10">10</option>
								</select>
							</th>
						</tr>
					<? endif; ?>
				</table>
			</th>
		</tr>
		<? if (count($parse['missions'])): ?>
			<tr>
				<th colspan="2"><input accesskey="z" value="<?=_getText('fl_continue') ?>" type="submit"></th>
			</tr>
		<? endif; ?>
	</table>
</form>
<script type="text/javascript">

	var mission = 0;

	$(document).ready(function()
	{
		mission = $('input[name=mission]:checked').val();
		durationTime = duration() * 1000;

		console.log(durationTime);

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