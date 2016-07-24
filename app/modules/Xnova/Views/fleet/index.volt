<div class="table-responsive">
	<table class='table'>
		<tr>
			<td colspan='9' class='c'>
				<table border="0" width="100%">
					<tr>
						<td align="left">
							<?=_getText('fl_title') ?> <?=$parse['maxFlyingFleets'] ?> <?=_getText('fl_sur') ?> <?=$parse['maxFlottes'] ?>
						</td>
						<td align="right">
							<?=$parse['currentExpeditions'] ?>/<?=$parse['maxExpeditions'] ?> <?=_getText('fl_expttl') ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th width='20'><?=_getText('fl_id') ?></th>
			<th><?=_getText('fl_mission') ?></th>
			<th><?=_getText('fl_count') ?></th>
			<th><?=_getText('fl_from') ?></th>
			<th width='80'><?=_getText('fl_start_t') ?></th>
			<th><?=_getText('fl_dest') ?></th>
			<th width='80'><?=_getText('fl_dest_t') ?></th>
			<th><?=_getText('fl_back_in') ?></th>
			<th width='110'><?=_getText('fl_order') ?></th>
		</tr>
		<? foreach ($parse['fleets'] as $i => $f): ?>
			<tr>
				<th><?=($i + 1) ?></th>
				<th>
					<a><?=_getText('type_mission', $f['mission']) ?></a>
					<? if (($f['start_time'] + 1) == $f['end_time']): ?>
						<br><a title="<?=_getText('fl_back_to_ttl') ?>"><?=_getText('fl_back_to') ?></a>
					<? else: ?>
						<br><a title="<?=_getText('fl_get_to_ttl') ?>"><?=_getText('fl_get_to') ?></a>
					<? endif; ?>
				</th>
				<th>
					<a class="tooltip" data-content='<? foreach ($f['fleet_array'] as $fleetId => $fleetData): ?><?=_getText('tech', $fleetId) ?>: <?=$fleetData['cnt'] ?><br><? endforeach; ?>'><?=\Xnova\Helpers::pretty_number($f['fleet_count']) ?></a>
				</th>
				<th><a href="<?=$this->url->get('galaxy/'.$f['start_galaxy'].'/'.$f['start_system'].'/') ?>">[<?=$f['start_galaxy'] ?>:<?=$f['start_system'] ?>:<?=$f['start_planet'] ?>]</a></th>
				<th><?=$this->game->datezone("d.m.y", $f['start_time']) ?><br><?=$this->game->datezone("H:i:s", $f['start_time']) ?></th>
				<th><a href="<?=$this->url->get('galaxy/'.$f['end_galaxy'].'/'.$f['end_system'].'/') ?>">[<?=$f['end_galaxy'] ?>:<?=$f['end_system'] ?>:<?=$f['end_planet'] ?>]</a></th>
				<th><?=$this->game->datezone("d.m.y", $f['end_time']) ?><br><?=$this->game->datezone("H:i:s", $f['end_time']) ?></th>
				<th><font color="lime"><?=($f['end_time'] > time() ? \Xnova\Helpers::pretty_time(floor($f['end_time'] + 1 - time())) : 'обработка...') ?></font></th>
				<th>
					<? if ($f['mess'] == 0 && $f['mission'] != 20 && $f['target_owner'] != 1): ?>
						<form action="<?=$this->url->get('fleet/back/') ?>" method="post">
							<input name="fleetid" value="<?=$f['id'] ?>" type="hidden">
							<input value=" <?=_getText('fl_back_to_ttl') ?> " type="submit" name="send">
						</form>
						<? if ($f['mission'] == 1): ?>
							<form action="<?=$this->url->get('fleet/verband/') ?>" method="post">
								<input name="fleetid" value="<?=$f['id'] ?>" type="hidden">
								<input value=" <?=_getText('fl_associate') ?> " type="submit">
							</form>
						<? endif; ?>
					<? elseif ($f['mess'] == 3 && $f['mission'] != 15): ?>
						<form action="<?=$this->url->get('fleet/back/') ?>" method="post">
							<input name="fleetid" value="<?=$f['id'] ?>" type="hidden">
							<input value=" Отозвать " type="submit" name="send">
						</form>
					<? else: ?>
						&nbsp;-&nbsp;
					<? endif; ?>
				</th>
			</tr>
		<? endforeach; ?>
		<? if (!count($parse['fleets'])): ?>
			<tr>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
				<th>-</th>
			</tr>
		<? endif; ?>
		<? if ($parse['maxFlyingFleets'] == $parse['maxFlottes']): ?>
			<tr><th colspan="9" class="negative"><?=_getText('fl_noslotfree') ?></th></tr>
		<? endif; ?>
	</table>
	</div>
	<br>
	<form action="<?=$this->url->get('fleet/stageone/') ?>" method="post">
		<div class="table fleet_ships container">
			<div class="row">
				<div class="col-xs-12 c">
					Выбрать корабли<?=$parse['mission_text'] ?>:
				</div>
			</div>
			<div class="row">
				<div class="th col-sm-5 col-xs-4"><?=_getText('fl_fleet_typ') ?></div>
				<div class="th col-sm-2 col-xs-2"><?=_getText('fl_fleet_disp') ?></div>
				<div class="th col-sm-2 col-xs-2">-</div>
				<div class="th col-sm-3 col-xs-4">-</div>
			</div>
			<? foreach ($parse['ships'] as $ship): ?>
				<div class="row">
					<div class="th col-sm-5 col-xs-4 middle"><a title="<?=_getText('tech', $ship['id']) ?>"><?=_getText('tech', $ship['id']) ?></a></div>
					<div class="th col-sm-2 col-xs-2 middle"><?=\Xnova\Helpers::pretty_number($ship['count']) ?></div>
					<? if ($ship['id'] == 212): ?>
						<div class="th col-sm-5 col-xs-6"></div>
					<? else: ?>
						<div class="th col-sm-2 col-xs-2 middle"><a href="javascript:noShip('ship<?=$ship['id'] ?>'); calc_capacity();">min</a>/<a href="javascript:maxShip('ship<?=$ship['id'] ?>'); calc_capacity();">max</a></div>
						<div class="th col-sm-3 col-xs-4">
							<a href="javascript:chShipCount('<?=$ship['id'] ?>', '-1'); calc_capacity();" title="Уменьшить на 1 ед." style="color:#FFD0D0">- </a>
							<input type="number" name="ship<?=$ship['id'] ?>" style="width:60%" value="0" onfocus="if(this.value == '0') this.value='';" onblur="if(this.value == '') this.value='0';" title="<?=_getText('tech', $ship['id']).': '.$ship['count'] ?>" onChange="calc_capacity()" onKeyUp="calc_capacity()" />
							<a href="javascript:chShipCount('<?=$ship['id'] ?>', '1'); calc_capacity();" title="Увеличить на 1 ед." style="color:#D0FFD0"> +</a>

							<input type="hidden" name="maxship<?=$ship['id'] ?>" value="<?=$ship['count'] ?>" />
							<input type="hidden" name="consumption<?=$ship['id'] ?>" value="<?=$ship['consumption'] ?>" />
							<input type="hidden" name="speed<?=$ship['id'] ?>" value="<?=$ship['speed'] ?>" />
							<input type="hidden" name="capacity<?=$ship['id'] ?>" value="<?=$ship['capacity'] ?>" />
						</div>
					<? endif; ?>
				</div>
			<? endforeach; ?>
			
			<? if (!count($parse['ships'])): ?>
				<div class="row">
					<div class="th col-xs-12"><?=_getText('fl_noships') ?></div>
				</div>
			<? else: ?>
				<div class="row">
					<div class="col-xs-6 col-sm-7 th">
						<a href="javascript:noShips(); calc_capacity();" ><?=_getText('fl_unselectall') ?></a>
					</div>
					<div class="col-xs-6 col-sm-5 th"><a href="javascript:maxShips(); calc_capacity();" ><?=_getText('fl_selectall') ?></a></div>
				</div>
				<div class="row">
					<div class="th col-xs-4 col-sm-7">-</div>
					<div class="th col-xs-4 col-sm-2">Вместимость</div>
					<div class="th col-xs-4 col-sm-3"><div id="allcapacity">-</div></div>
				</div>
				<div class="row">
					<div class="th col-xs-4 col-sm-7">-</div>
					<div class="th col-xs-4 col-sm-2">Скорость</div>
					<div class="th col-xs-4 col-sm-3"><div id="allspeed">-</div></div>
				</div>
				<? if ($parse['maxFlyingFleets'] < $parse['maxFlottes']): ?>
					<div class="row">
						<div class="th col-xs-12"><input type="submit" value=" <?=_getText('fl_continue') ?> " /></div>
					</div>
				<? endif; ?>
			<? endif; ?>
		</div>
		<input type="hidden" name="galaxy" value="<?=$parse['galaxy'] ?>" />
		<input type="hidden" name="system" value="<?=$parse['system'] ?>" />
		<input type="hidden" name="planet" value="<?=$parse['planet'] ?>" />
		<input type="hidden" name="planet_type" value="<?=$parse['planettype'] ?>" />
		<input type="hidden" name="mission" value="<?=$parse['mission'] ?>" />
		<input type="hidden" name="maxepedition" value="<?=$parse['maxExpeditions'] ?>" />
		<input type="hidden" name="curepedition" value="<?=$parse['currentExpeditions'] ?>" />
		<input type="hidden" name="target_mission" value="<?=$parse['mission'] ?>" />
		<input type="hidden" name="crc" value="<?=md5($this->user->getId() . '-CHeAT_CoNTROL_Stage_01-' . date("dmY", time())) ?>" />
	</form>
