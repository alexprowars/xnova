<script type="text/javascript">
	$(document).ready(function()
	{
		shortInfo();
	});
</script>

<form action="{{ url('fleet/stagetwo/') }}" method="post">
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
	<div class="table">
		<div class="row">
			<div class="c col-xs-12"><?=_getText('fl_floten1_ttl') ?></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_dest') ?></div>
			<div class="th col-xs-6 fleet-coordinates-input">
				<input type="number" name="galaxy" min="1" max="<?=$this->config->game->maxGalaxyInWorld ?>" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['galaxyend'] ?>" title="">
				<input type="number" name="system" min="1" max="<?=$this->config->game->maxSystemInGalaxy ?>" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['systemend'] ?>" title="">
				<input type="number" name="planet" min="1" max="<?=($this->config->game->maxPlanetInSystem + 1) ?>" onChange="shortInfo()" onKeyUp="shortInfo()" value="<?=$parse['planetend'] ?>" title="">
				<select name="planettype" onChange="shortInfo()" onKeyUp="shortInfo()" title="">
					<? foreach (_getText('type_planet') AS $key => $value): ?>
						<option value="<?=$key ?>"<?=(($parse['typeend'] == $key) ? " SELECTED" : "") ?>><?=$value ?></option>
					<? endforeach; ?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_speed') ?></div>
			<div class="th col-xs-6">
				<select name="speed" onChange="shortInfo()" onKeyUp="shortInfo()" title="">
					<? foreach ($parse['speed'] as $a => $b): ?>
						<option value="<?=$a ?>"><?=$b ?></option>
					<? endforeach; ?>
				</select> %
			</div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_dist') ?></div>
			<div class="th col-xs-6"><div id="distance">-</div></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_fltime') ?></div>
			<div class="th col-xs-6"><div id="duration">-</div></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_time_go') ?></div>
			<div class="th col-xs-6"><div id="end_time">-</div></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_deute_need') ?></div>
			<div class="th col-xs-6"><div id="consumption">-</div></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_speed_max') ?></div>
			<div class="th col-xs-6"><div id="maxspeed">-</div></div>
		</div>
		<div class="row">
			<div class="th col-xs-6"><?=_getText('fl_max_load') ?></div>
			<div class="th col-xs-6"><div id="storage">-</div></div>
		</div>
		<div class="row">
			<div class="c col-xs-12"><?=_getText('fl_shortcut') ?> <a href="{{ url('fleet/shortcut/') }}"><?=_getText('fl_shortlnk') ?></a></div>
		</div>
		<? if (count($parse['shortcut'])): ?>
			<div class="row">
				<? foreach ($parse['shortcut'] AS $i => $c): ?>
					<? if ($i > 0 && $i%2 == 0): ?></div><div class="row"><? endif; ?>
					<div class="th col-xs-6">
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
					</div>
				<? endforeach; ?>
				<? if ($i%2 == 0): ?><div class="th col-xs-6">&nbsp;</div><? endif; ?>
			</div>
		<? endif; ?>
		<? if (count($parse['planets'])): ?>
			<div class="row">
				<div class="c col-xs-12"><?=_getText('fl_myplanets') ?></div>
			</div>
			<div class="row">
				<? foreach ($parse['planets'] AS $i => $row): ?>
					<? if ($i > 0 && $i%2 == 0): ?></div><div class="row"><? endif; ?>
					<div class="th col-xs-6">
						<a href="javascript:setTarget(<?=$row['galaxy'] ?>,<?=$row['system'] ?>,<?=$row['planet'] ?>,<?=$row['planet_type'] ?>); shortInfo();">
							<?=$row['name'] ?> <?=$row['galaxy'] ?>:<?=$row['system'] ?>:<?=$row['planet'] ?>
						</a>
					</div>
				<? endforeach; ?>
				<? if ($i%2 == 0): ?><div class="th col-xs-6">&nbsp;</div><? endif; ?>
			</div>
		<? endif; ?>
		<? if (($parse['thistype'] == 3 || $parse['thistype'] == 5) && count($parse['moons']) > 0): ?>
			<div class="row">
				<div class="c col-xs-12">
					<?=_getText('fl_jumpgate') ?><? if ($parse['moon_timer'] != ''): ?> - <span id="bxxGate1"></span><?=$parse['moon_timer'] ?><? endif; ?>
				</div>
			</div>
			<div class="row">
				<? foreach ($parse['moons'] AS $i => $moon): ?>
					<? if ($i > 0 && $i%2 == 0): ?></div><div class="row"><? endif; ?>
					<div class="th col-xs-6">
						<input type="radio" name="moon" value="<?=$moon['id'] ?>" id="moon<?=$moon['id'] ?>">
						<label for="moon<?=$moon['id'] ?>"><?=$moon['name'] ?> [<?=$moon['galaxy'] ?>:<?=$moon['system'] ?>:<?=$moon['planet'] ?>] <?=\Xnova\Helpers::pretty_time($moon['timer']) ?></label>
					</div>
				<? endforeach; ?>
				<? if ($i%2 == 0): ?><div class="th col-xs-6">&nbsp;</div><? endif; ?>
			</div>
		<? endif; ?>
		<? if (count($parse['aks']) > 0): ?>
			<div class="row">
				<div class="c col-xs-12"><?=_getText('fl_grattack') ?></div>
			</div>
			<? foreach ($parse['aks'] AS $i => $row): ?>
				<div class="row">
					<div class="th col-xs-12">
						<a href="javascript:setTarget(<?=$row['galaxy'] ?>,<?=$row['system'] ?>,<?=$row['planet'] ?>,<?=$row['planet_type'] ?>);shortInfo(); ACS(<?=$row['id'] ?>);">(<?=$row['name'] ?>)</a>
					</div>
				</div>
			<? endforeach; ?>
		<? endif; ?>
		<div class="row">
			<div class="th col-xs-12"><input type="submit" value="<?=_getText('fl_continue') ?>"></div>
		</div>
	</div>
	<input type="hidden" name="acs" value="0" />
	<input type="hidden" name="maxepedition" value="<?=$parse['maxepedition'] ?>" />
	<input type="hidden" name="curepedition" value="<?=$parse['curepedition'] ?>" />
	<input type="hidden" name="target_mission" value="<?=$parse['target_mission'] ?>" />
	<input type="hidden" name="crc" value="<?=$parse['crc'] ?>" />
</form>
	