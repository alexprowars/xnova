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
					<a><?=_getText('type_mission', $f['fleet_mission']) ?></a>
					<? if (($f['fleet_start_time'] + 1) == $f['fleet_end_time']): ?>
						<br><a title="<?=_getText('fl_back_to_ttl') ?>"><?=_getText('fl_back_to') ?></a>
					<? else: ?>
						<br><a title="<?=_getText('fl_get_to_ttl') ?>"><?=_getText('fl_get_to') ?></a>
					<? endif; ?>
				</th>
				<th>
					<a class="tooltip" data-tooltip-content='<? foreach ($f['fleet_array'] as $fleetId => $fleetData): ?><?=_getText('tech', $fleetId) ?>: <?=$fleetData['cnt'] ?><br><? endforeach; ?>'><?=\Xcms\strings::pretty_number($f['fleet_count']) ?></a>
				</th>
				<th><a href="?set=galaxy&r=3&galaxy=<?=$f['fleet_start_galaxy'] ?>&system=<?=$f['fleet_start_system'] ?>">[<?=$f['fleet_start_galaxy'] ?>:<?=$f['fleet_start_system'] ?>:<?=$f['fleet_start_planet'] ?>]</a></th>
				<th><?=datezone("d.m.y", $f['fleet_start_time']) ?><br><?=datezone("H:i:s", $f['fleet_start_time']) ?></th>
				<th><a href="?set=galaxy&r=3&galaxy=<?=$f['fleet_end_galaxy'] ?>&system=<?=$f['fleet_end_system'] ?>">[<?=$f['fleet_end_galaxy'] ?>:<?=$f['fleet_end_system'] ?>:<?=$f['fleet_end_planet'] ?>]</a></th>
				<th><?=datezone("d.m.y", $f['fleet_end_time']) ?><br><?=datezone("H:i:s", $f['fleet_end_time']) ?></th>
				<th><font color="lime"><?=($f['fleet_end_time'] > time() ? \Xcms\strings::pretty_time(floor($f['fleet_end_time'] + 1 - time())) : 'обработка...') ?></font></th>
				<th>
					<? if ($f['fleet_mess'] == 0 && $f['fleet_mission'] != 20 && $f['fleet_target_owner'] != 1): ?>
						<form action="?set=fleet&page=back" method="post">
							<input name="fleetid" value="<?=$f['fleet_id'] ?>" type="hidden">
							<input value=" <?=_getText('fl_back_to_ttl') ?> " type="submit" name="send">
						</form>
						<? if ($f['fleet_mission'] == 1): ?>
							<form action="?set=fleet&page=verband" method="post">
								<input name="fleetid" value="<?=$f['fleet_id'] ?>" type="hidden">
								<input value=" <?=_getText('fl_associate') ?> " type="submit">
							</form>
						<? endif; ?>
					<? elseif ($f['fleet_mess'] == 3 && $f['fleet_mission'] != 15): ?>
						<form action="?set=fleet&page=back" method="post">
							<input name="fleetid" value="<?=$f['fleet_id'] ?>" type="hidden">
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
			<tr><th colspan="9"><font color="red"><?=_getText('fl_noslotfree') ?></font></th></tr>
		<? endif; ?>
	</table>
	</div>
	<br>
	<form action="?set=fleet&page=fleet_1" method="post" class="table-responsive">
		<table class="table fleet_ships">
			<tr>
				<td colspan="4" class="c">
					Выбрать корабли<?=$parse['mission_text'] ?>:
				</td>
			</tr>
			<tr>
				<th class="col-sm-5 col-xs-4"><?=_getText('fl_fleet_typ') ?></th>
				<th class="col-sm-2 col-xs-2"><?=_getText('fl_fleet_disp') ?></th>
				<th class="col-sm-2 col-xs-2">-</th>
				<th class="col-sm-3 col-xs-4">-</th>
			</tr>
			<? foreach ($parse['ships'] as $ship): ?>
				<tr>
					<th><a title="<?=_getText('tech', $ship['id']) ?>"><?=_getText('tech', $ship['id']) ?></a></th>
					<th><?=\Xcms\strings::pretty_number($ship['count']) ?></th>
					<? if ($ship['id'] == 212): ?>
						<th></th><th></th>
					<? else: ?>
						<th><a href="javascript:noShip('ship<?=$ship['id'] ?>'); calc_capacity();">min</a>/<a href="javascript:maxShip('ship<?=$ship['id'] ?>'); calc_capacity();">max</a></th>
						<th>
							<a href="javascript:chShipCount('<?=$ship['id'] ?>', '-1'); calc_capacity();" title="Уменьшить на 1 ед." style="color:#FFD0D0">- </a>
							<input type="text" name="ship<?=$ship['id'] ?>" style="width:65%" value="0" onfocus="if(this.value == '0') this.value='';" onblur="if(this.value == '') this.value='0';" title="<?=_getText('tech', $ship['id']).': '.$ship['count'] ?>" onChange="calc_capacity()" onKeyUp="calc_capacity()" />
							<a href="javascript:chShipCount('<?=$ship['id'] ?>', '1'); calc_capacity();" title="Увеличить на 1 ед." style="color:#D0FFD0"> +</a>

							<input type="hidden" name="maxship<?=$ship['id'] ?>" value="<?=$ship['count'] ?>" />
							<input type="hidden" name="consumption<?=$ship['id'] ?>" value="<?=$ship['consumption'] ?>" />
							<input type="hidden" name="speed<?=$ship['id'] ?>" value="<?=$ship['speed'] ?>" />
							<input type="hidden" name="capacity<?=$ship['id'] ?>" value="<?=$ship['capacity'] ?>" />
						</th>
					<? endif; ?>
				</tr>
			<? endforeach; ?>
			
			<? if (!count($parse['ships'])): ?>
				<tr>
					<th colspan="4"><?=_getText('fl_noships') ?></th>
				</tr>
				<tr>
					<th colspan="4"><input type="submit" value=" <?=_getText('fl_continue') ?> " /></th>
				</tr>
			<? else: ?>
				<tr>
					<th colspan="2">
						<a href="javascript:noShips(); calc_capacity();" ><?=_getText('fl_unselectall') ?></a>
					</th>
					<th colspan="2"><a href="javascript:maxShips(); calc_capacity();" ><?=_getText('fl_selectall') ?></a></th>
				</tr>
				<tr>
					<th colspan="2" class="hidden-xs">-</th>
					<th colspan="1" class="visible-xs">-</th>
					<th colspan="1" class="hidden-xs">Вместимость</th>
					<th colspan="2" class="visible-xs">Вместимость</th>
					<th colspan="1"><div id="allcapacity">-</div></th>
				</tr>
				<tr>
					<th colspan="2" class="hidden-xs">-</th>
					<th colspan="1" class="visible-xs">-</th>
					<th colspan="1" class="hidden-xs">Скорость</th>
					<th colspan="2" class="visible-xs">Скорость</th>
					<th colspan="1"><div id="allspeed">-</div></th>
				</tr>
				<? if ($parse['maxFlyingFleets'] < $parse['maxFlottes']): ?>
				<tr>
					<th colspan="4"><input type="submit" value=" <?=_getText('fl_continue') ?> " /></th>
				</tr>
				<? endif; ?>
			<? endif; ?>
		</table>
		<input type="hidden" name="galaxy" value="<?=$parse['galaxy'] ?>" />
		<input type="hidden" name="system" value="<?=$parse['system'] ?>" />
		<input type="hidden" name="planet" value="<?=$parse['planet'] ?>" />
		<input type="hidden" name="planet_type" value="<?=$parse['planettype'] ?>" />
		<input type="hidden" name="mission" value="<?=$parse['mission'] ?>" />
		<input type="hidden" name="maxepedition" value="<?=$parse['maxExpeditions'] ?>" />
		<input type="hidden" name="curepedition" value="<?=$parse['currentExpeditions'] ?>" />
		<input type="hidden" name="target_mission" value="<?=$parse['mission'] ?>" />
		<input type="hidden" name="crc" value="<?=md5(\Xnova\user::get()->getId() . '-CHeAT_CoNTROL_Stage_01-' . date("dmY", time())) ?>" />
	</form>
