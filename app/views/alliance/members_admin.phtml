<table class="table">
	<tr>
		<td class="c" colspan="10">Список членов альянса (количество: <?=$parse['i'] ?>)</td>
	</tr>
	<tr>
		<th>№</th>
		<th><a href="?set=alliance&<?=(($parse['admin']) ? 'mode=admin&edit=members' : 'mode=memberslist') ?>&sort1=1&sort2=<?=$parse['s'] ?>">Ник</a></th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th><a href="?set=alliance&<?=(($parse['admin']) ? 'mode=admin&edit=members' : 'mode=memberslist') ?>&sort1=2&sort2=<?=$parse['s'] ?>">Ранг</a></th>
		<th><a href="?set=alliance&<?=(($parse['admin']) ? 'mode=admin&edit=members' : 'mode=memberslist') ?>&sort1=3&sort2=<?=$parse['s'] ?>">Очки</a></th>
		<th>Координаты</th>
		<th><a href="?set=alliance&<?=(($parse['admin']) ? 'mode=admin&edit=members' : 'mode=memberslist') ?>&sort1=4&sort2=<?=$parse['s'] ?>">Дата вступления</a></th>
		<? if ($parse['status']): ?><th><a href="?set=alliance&<?=(($parse['admin']) ? 'mode=admin&edit=members' : 'mode=memberslist') ?>&sort1=5&sort2=<?=$parse['s'] ?>">Активность</a></th><? endif; ?>
		<? if ($parse['admin']): ?><th>Управление</th><? endif; ?>
	</tr>
	<? foreach ($parse['memberslist'] AS $m): ?>
		<? if (!isset($m['Rank_for']) || !$parse['admin']): ?>
			<tr>
				<th><?=$m['i'] ?></th>
				<th><?=$m['username'] ?></th>
				<th><a href="javascript:;" onclick="showWindow('<?=$m['username'] ?>: отправить сообщение', '?set=messages&mode=write&id=<?=$m['id'] ?>&ajax&popup', 680)" title="Написать сообщение"><span class='sprite skin_m'></span></a></th>
				<th><img src="/skins/default/images/race<?=$m['race'] ?>.gif" width="16" height="16"></th>
				<th><?=$m['ally_range'] ?></th>
				<th><?=$m['points'] ?></th>
				<th><a href="?set=galaxy&r=3&galaxy=<?=$m['galaxy'] ?>&system=<?=$m['system'] ?>"><?=$m['galaxy'] ?>:<?=$m['system'] ?>:<?=$m['planet'] ?></a></th>
				<th><?=$m['time'] ?></th>
				<? if ($parse['status']): ?><th><font color=<?=$m['onlinetime'] ?>/font></th><? endif; ?>
				<? if ($parse['admin']): ?><th><a href="?set=alliance&mode=admin&edit=members&kick=<?=$m['id'] ?>" onclick="javascript:return confirm('Вы действительно хотите исключить данного игрока из альянса?');"><img src="<?=RPATH ?><?=DPATH ?>pic/abort.gif"></a>&nbsp;<a href="?set=alliance&mode=admin&edit=members&rank=<?=$m['id'] ?>"><img src="<?=RPATH ?><?=DPATH ?>pic/key.gif"></a></th><? endif; ?>
			</tr>
		<? else: ?>
			<tr>
				<td colspan="10">
					<form action="?set=alliance&mode=admin&edit=members&id=<?=$m['id'] ?>" method=POST>
						<table class="table">
							<tr>
								<th colspan="7"><?=$m['Rank_for'] ?></th>
								<th><select name="newrang"><?=$m['options'] ?></select></th>
								<th colspan=2><input type=submit value="Сохранить"></th>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		<? endif; ?>
	<? endforeach; ?>
	<tr>
		<td class="c" colspan="10"><a href="?set=alliance<?=(($parse['admin']) ? '&mode=admin&edit=ally' : '') ?>"><?=_getText('Return_to_overview') ?></a></td>
	</tr>
</table>
