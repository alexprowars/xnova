<table class="table">
	<tr>
		<td colspan="9" class="c">Флоты в совместной атаке</td>
	</tr>
	<tr>
		<th>ID</th>
		<th>Задание</th>
		<th> Кол-во</th>
		<th>Отправлен</th>
		<th>Прибытие (цель)</th>
		<th>Цель</th>
		<th>Прибытие (возврат)</th>
		<th>Прибудет через</th>
		<th>Планета старта</th>
	</tr>
	<? foreach ($parse['list'] as $i => $item): /** * @var $item \Xnova\Models\Fleet  */ ?>
		<tr>
			<th><?=($i+1) ?></th>
			<th>
				<a><?=_getText('type_mission', $item->mission) ?></a>
				<? if (($item->start_time + 1) == $item->end_time): ?>
					<a>(F)</a>
				<? endif; ?>
			</th>
			<th>
				<a class="tooltip" data-content="<? foreach ($item->getShips() as $t => $f): ?><?=_getText('tech', $t) ?>: <?=$f['cnt'] ?><br><? endforeach; ?>">
					<?=\Xnova\Helpers::pretty_number($item->getTotalShips()) ?>
				</a>
			</th>
			<th><?=$item->getStartAdressLink() ?></th>
			<th><?=$this->game->datezone("d.m H:i:s", $item->start_time) ?></th>
			<th><?=$item->getTargetAdressLink() ?></th>
			<th><?=$this->game->datezone("d.m H:i:s", $item->end_time) ?></th>
			<th>
				<div id="time_0" class="positive"><?=\Xnova\Helpers::pretty_time(floor($item->end_time + 1 - time())) ?></div>
			</th>
			<th><?=$item->owner_name ?></th>
		</tr>
	<? endforeach; ?>
	<? if (count($parse['list'])): ?>
		<tr><th colspan="9">-</th></tr>
	<? endif; ?>
</table>

<? if ($parse['group'] == 0): ?>
	<div class="separator"></div>
	<form action="{{ url('fleet/verband/') }}" method="POST">
		<input type="hidden" name="fleetid" value="<?=$parse['fleetid'] ?>" />
		<input type="hidden" name="action" value="addaks" />
		<table class="table">
		<tr>
			<td class="c" colspan="2">Создание ассоциации флота</td>
		</tr>
		<tr>
			<th colspan="2">
				<input type="text" name="groupname" value="AKS<?=mt_rand(100000, 999999999) ?>" size=50 title="">
				<br />
				<input type="submit" value="Создать" />
			</th>
		</tr>
		</table>
	</form>
<? elseif (isset($parse['aks']) && $parse['fleetid'] == $parse['aks']['fleet_id']): ?>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="2">Ассоциация флота <?=$parse['aks']['name'] ?></td>
		</tr>
		<tr>
			<th colspan="2">
				<form action="{{ url('fleet/verband/') }}" method="POST">
					<input type="hidden" name="fleetid" value="<?=$parse['fleetid'] ?>"/>
					<input type="hidden" name="action" value="changename"/>
					<input type="text" name="groupname" value="<?=$parse['aks']['name'] ?>" size=50 title="">
					<br/>
					<input type="submit" value="Изменить"/>
				</form>
			</th>
		</tr>
		<tr>
			<th>
				<table class="table">
					<tr>
						<td class="c">Приглашенные участники</td>
						<td class="c">Пригласить участников</td>
					</tr>
					<tr>
						<th width="50%" valign="top">
							<select size="10" style="width:100%;" title="">
								<? if (isset($parse['users']) && count($parse['users'])): ?>
									<? foreach ($parse['users'] as $user): ?>
										<option><?=$user ?></option>
									<? endforeach; ?>
								<? else: ?>
									<option>нет участников</option>
								<? endif; ?>
							</select>
						</th>
						<th>
							<form action="{{ url('fleet/verband/') }}" method="POST">
								<input type="hidden" name="fleetid" value="<?=$parse['fleetid'] ?>" />
								<input type="hidden" name="action" value="adduser" />
								<? if (count($parse['friends'])): ?>
									Список друзей:<br>
									<select name="userid" size="5" style="width:50%;" title="">
										<? foreach ($parse['friends'] as $user): ?>
											<option value="<?=$user['id'] ?>"><?=$user['username'] ?></option>
										<? endforeach; ?>
									</select>
									<br><br>
								<? endif; ?>
								<? if (count($parse['alliance'])): ?>
									Члены альянса:<br>
									<select name="userid" size="5" style="width:50%;" title="">
										<? foreach ($parse['alliance'] as $user): ?>
											<option value="<?=$user['id'] ?>"><?=$user['username'] ?></option>
										<? endforeach; ?>
									</select>
									<br><br>
								<? endif; ?>
								<input type="text" name="addtogroup" size="40" placeholder="Введите игровой ник" />
								<br>
								<input type="submit" value="OK" />
							</form>
						</th>
					</tr>
				</table>
			</th>
		</tr>
	</table>
<? endif; ?>