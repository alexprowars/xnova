<table class="table">
	<tr>
		<td class=c colspan=6><?=$parse['title'] ?></td>
	</tr>

	<? if (!$parse['a']): ?>
	<tr>
		<th colspan=6><a href=?set=buddy&a=1>Запросы</a></th>
	</tr>
	<tr>
		<th colspan=6><a href=?set=buddy&a=1&e=1>Мои запросы</a></th>
	</tr>
	<tr>
		<td class=c>&nbsp;</td>
		<td class=c>Имя</td>
		<td class=c>Альянс</td>
		<td class=c>Координаты</td>
		<td class=c>Позиция</td>
		<td class=c>&nbsp;</td>
	</tr>
	<? else: ?>
	<tr>
		<td class=c>&nbsp;</td>
		<td class=c>Пользователь</td>
		<td class=c>Альянс</td>
		<td class=c>Координаты</td>
		<td class=c>Текст</td>
		<td class=c>&nbsp;</td>
	</tr>
	<? endif; ?>

	<? if (count($parse['list'])): ?>
	<? foreach ($parse['list'] AS $id => $list): ?>
		<tr>
			<th width="20"><?=($id + 1) ?></th>
			<th><a href=?set=messages&mode=write&id=<?=$list['id'] ?>><?=$list['username'] ?></a></th>
			<th><?=$list['ally'] ?></th>
			<th><a href="?set=galaxy&r=3&galaxy=<?=$list['g'] ?>&system=<?=$list['s'] ?>"><?=$list['g'] ?>:<?=$list['s'] ?>:<?=$list['p'] ?></a></th>
			<th><?=$list['online'] ?></th>
			<th><?=$list['c'] ?></th>
		</tr>
		<? endforeach; ?>
	<? else: ?>
	<tr>
		<th colspan=6>Нет друзей</th>
	</tr>
	<? endif; ?>
	<? if ($parse['a']): ?>
	<tr>
		<td colspan=6 class=c><a href="?set=buddy">назад</a></td>
	</tr>
	<? endif; ?>

</table>

<? if (isset($okFriends) && count($okFriends)): ?>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="4">Ваши друзья в Одноклассниках</td>
		</tr>
		<tr>
			<? foreach ($okFriends AS $ii => $data): ?>
				<? if ($ii > 0 && $ii%5 == 0): ?></tr><tr><? endif; ?>
				<th width="20%">
					<div class="separator"></div>
					<table width="100%">
						<tr>
						  <td style="text-align:center;">
							<img src="<?=$okArray[$data['ok_uid']]['pic128x128'] ?>" style="max-width:96px">
						  </td>
						</tr>
						<tr>
						  <td style="text-align:center;">
							  <?=$okArray[$data['ok_uid']]['name'] ?>
							  <? if ($okArray[$data['ok_uid']]['name'] != $data['username']): ?><br>[<?=$data['username'] ?>]<? endif; ?>
							  <a href="javascript:;" onclick="showWindow('<?=$data['username'] ?>: отправить сообщение', '?set=messages&mode=write&id=<?=$data['id'] ?>&ajax&popup', 680)" data-link="1" title="Сообщение"><span class='sprite skin_m'></span></a>
						  </td>
						</tr>
						<tr>
							<td>
								<?=$data['planet_name'] ?><br>
								<a href="?set=galaxy&r=3&galaxy=<?=$data['galaxy'] ?>&system=<?=$data['system'] ?>">[<?=$data['galaxy'] ?>:<?=$data['system'] ?>:<?=$data['planet'] ?>]</a>
							</td>
					  </tr>
					</table>
					<div class="separator"></div>
				</th>
			<? endforeach; ?>
		</tr>
	</table>
<? endif; ?>