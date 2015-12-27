<script type="text/javascript">
	$(document).ready(function()
	{
		shortInfo();
	});
</script>

<form action="?set=fleet&page=fleet_2" method="post">
	<? foreach ($parse['ships'] as $ship): ?>
		<input type="hidden" name="ship<?=$ship['id'] ?>" value="<?=$ship['count'] ?>" />
		<input type="hidden" name="consumption<?=$ship['id'] ?>" value="<?=$ship['consumption'] ?>" />
		<input type="hidden" name="speed<?=$ship['id'] ?>" value="<?=$ship['speed'] ?>" />
		<input type="hidden" name="capacity<?=$ship['id'] ?>" value="<?=$ship['capacity'] ?>" />
	<? endforeach; ?>
	<input type="hidden" name="usedfleet"      value="<?=$parse['usedfleet'] ?>" />
	<input type="hidden" name="thisgalaxy"     value="<?=$parse['thisgalaxy'] ?>" />
	<input type="hidden" name="thissystem"     value="<?=$parse['thissystem'] ?>" />
	<input type="hidden" name="thisplanet"     value="<?=$parse['thisplanet'] ?>" />
	<input type="hidden" name="galaxyend"      value="<?=$parse['galaxyend'] ?>" />
	<input type="hidden" name="systemend"      value="<?=$parse['systemend'] ?>" />
	<input type="hidden" name="planetend"      value="<?=$parse['planetend'] ?>" />
	<input type="hidden" name="speedfactor"    value="" />
	<input type="hidden" name="thisresource1"  value="<?=$parse['thisresource1'] ?>" />
	<input type="hidden" name="thisresource2"  value="<?=$parse['thisresource2'] ?>" />
	<input type="hidden" name="thisresource3"  value="<?=$parse['thisresource3'] ?>" />
	<br>
	<div>
		<center>
			<table class="table">
				<tr>
					<td colspan="2" class="c"><?=_getText('fl_floten1_ttl') ?></td>
				</tr>
				<tr>
					<th class="col-xs-6"><?=_getText('fl_dest') ?></th>
					<th class="col-xs-6">
						<input type="text" name="galaxy" size="3" maxlength="2" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['galaxyend'] ?>" />
						<input type="text" name="system" size="3" maxlength="3" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['systemend'] ?>" />
						<input type="text" name="planet" size="3" maxlength="2" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['planetend'] ?>" />
						<select name="planettype" onChange="shortInfo()" onKeyUp="shortInfo()">
							<? foreach (_getText('type_planet') AS $key => $value): ?>
								<option value="<?=$key ?>"<?=(($parse['typeend'] == $key) ? " SELECTED" : "") ?>><?=$value ?></option>
							<? endforeach; ?>
						</select>
					</th>
				</tr>
				<tr>
					<th><?=_getText('fl_speed') ?></th>
					<th>
						<select name="speed" onChange="shortInfo()" onKeyUp="shortInfo()">
							<? foreach ($parse['speed'] as $a => $b): ?>
								<option value="<?=$a ?>"><?=$b ?></option>
							<? endforeach; ?>
						</select> %
					</th>
				</tr>
				<tr>
					<th><?=_getText('fl_dist') ?></th>
					<th><div id="distance">-</div></th>
				</tr>
				<tr>
					<th><?=_getText('fl_fltime') ?></th>
					<th><div id="duration">-</div></th>
				</tr>
				<tr>
					<th><?=_getText('fl_time_go') ?></th>
					<th><div id="end_time">-</div></th>
				</tr>
				<tr>
					<th><?=_getText('fl_deute_need') ?></th>
					<th><div id="consumption">-</div></th>
				</tr>
				<tr>
					<th><?=_getText('fl_speed_max') ?></th>
					<th><div id="maxspeed">-</div></th>
				</tr>
				<tr>
					<th><?=_getText('fl_max_load') ?></th>
					<th><div id="storage">-</div></th>
				</tr>
				<tr>
					<td colspan="2" class="c"><?=_getText('fl_shortcut') ?> <a href="?set=fleet&page=shortcut"><?=_getText('fl_shortlnk') ?></a></td>
				</tr>
				<? if (count($parse['shortcut'])): ?>
					<tr>
						<? foreach ($parse['shortcut'] AS $i => $c): ?>
							<? if ($i > 0 && $i%2 == 0): ?></tr><tr><? endif; ?>
							<th>
								<a href="javascript:setTarget(<?=$c[1] ?>,<?=$c[2] ?>,<?=$c[3] ?>,<?=$c[4] ?>); shortInfo();">
									<?=$c[0] ?> <?=$c[1] ?>:<?=$c[2] ?>:<?=$c[3] ?>
									<? if ($c[4] == 1): ?>
										<?=_getText('fl_shrtcup1') ?>
									<? elseif ($c[4] == 2): ?>
										<?=_getText('fl_shrtcup2') ?>
									<? elseif ($c[4] == 3): ?>
										<?=_getText('fl_shrtcup3') ?>
									<? endif; ?>
								</a>
							</th>
						<? endforeach; ?>
						<? if ($i%2 == 0): ?><th>&nbsp;</th><? endif; ?>
					</tr>
				<? endif; ?>
				<? if (count($parse['planets'])): ?>
					<tr>
						<td colspan="2" class="c"><?=_getText('fl_myplanets') ?></td>
					</tr>
					<tr>
						<? foreach ($parse['planets'] AS $i => $row): ?>
							<? if ($i > 0 && $i%2 == 0): ?></tr><tr><? endif; ?>
							<th>
								<a href="javascript:setTarget(<?=$row['galaxy'] ?>,<?=$row['system'] ?>,<?=$row['planet'] ?>,<?=$row['planet_type'] ?>); shortInfo();">
									<?=$row['name'] ?> <?=$row['galaxy'] ?>:<?=$row['system'] ?>:<?=$row['planet'] ?>
								</a>
							</th>
						<? endforeach; ?>
						<? if ($i%2 == 0): ?><th>&nbsp;</th><? endif; ?>
					<tr>
				<? endif; ?>
				<? if (($parse['thistype'] == 3 || $parse['thistype'] == 5) && count($parse['moons']) > 0): ?>
					<tr>
						<td colspan="2" class="c">
							<?=_getText('fl_jumpgate') ?><? if ($parse['moon_timer'] != ''): ?> - <span id="bxxGate1"></span><?=$parse['moon_timer'] ?><? endif; ?>
						</td>
					</tr>
					<tr>
						<? foreach ($parse['moons'] AS $i => $moon): ?>
							<? if ($i > 0 && $i%2 == 0): ?></tr><tr><? endif; ?>
							<th>
								<input type="radio" name="moon" value="<?=$moon['id'] ?>" id="moon<?=$moon['id'] ?>">
								<label for="moon<?=$moon['id'] ?>"><?=$moon['name'] ?> [<?=$moon['galaxy'] ?>:<?=$moon['system'] ?>:<?=$moon['planet'] ?>] <?=$moon['timer']['string'] ?></label>
							</th>
						<? endforeach; ?>
						<? if ($i%2 == 0): ?><th>&nbsp;</th><? endif; ?>
					</tr>
				<? endif; ?>
				<? if (count($parse['aks']) > 0): ?>
					<tr>
						<td colspan="2" class="c"><?=_getText('fl_grattack') ?></td>
					</tr>
					<? foreach ($parse['aks'] AS $i => $row): ?>
						<tr>
							<th colspan="2">
								<a href="javascript:setTarget(<?=$row['galaxy'] ?>,<?=$row['system'] ?>,<?=$row['planet'] ?>,<?=$row['planet_type'] ?>);shortInfo(); ACS(<?=$row['id'] ?>);">(<?=$row['name'] ?>)</a>
							</th>
						</tr>
					<? endforeach; ?>
				<? endif; ?>
				<tr>
					<th colspan="2"><input type="submit" value="<?=_getText('fl_continue') ?>" /></th>
				</tr>
			</table>
		</center>
	</div>
	<input type="hidden" name="acs" value="0" />
	<input type="hidden" name="maxepedition" value="<?=$parse['maxepedition'] ?>" />
	<input type="hidden" name="curepedition" value="<?=$parse['curepedition'] ?>" />
	<input type="hidden" name="target_mission" value="<?=$parse['target_mission'] ?>" />
	<input type="hidden" name="crc" value="<?=$parse['crc'] ?>" />
</form>
	