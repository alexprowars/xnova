<?
/**
 * @var $parse array
 */
?>
<form action="<?=$this->url->get('search/') ?>" method="post">
	<table class="table">
		<tr>
			<td class="c">Поиск по игре</td>
		</tr>
		<tr>
			<th>
				<select name="type" title="">
					<option value="playername"<?=(($parse['type'] == "playername") ? " SELECTED" : "") ?>>Логин игрока</option>
					<option value="planetname"<?=(($parse['type'] == "planetname") ? " SELECTED" : "") ?>>Название планеты</option>
					<option value="allytag"<?=(($parse['type'] == "allytag") ? " SELECTED" : "") ?>>Аббревиатура альянса</option>
					<option value="allyname"<?=(($parse['type'] == "allyname") ? " SELECTED" : "") ?>>Название альянса</option>
				</select>
				&nbsp;&nbsp;
				<input type="text" name="searchtext" value="<?=$parse['searchtext'] ?>" title="">
				&nbsp;&nbsp;

				<input type="submit" value="Поиск">
			</th>
		</tr>
	</table>
</form>
<div class="separator"></div>
<? if ($parse['searchtext'] != ''): ?>
	<? if (isset($parse['type']) && ($parse['type'] == 'playername' || $parse['type'] == 'planetname')): ?>
		<table class="table">
			<tr>
				<td class="c" width="120">Имя</td>
				<td class="c" width="40">&nbsp;</td>
				<td class="c" width="20">&nbsp;</td>
				<td class="c">Альянс</td>
				<td class="c">Планета</td>
				<td class="c" width="80">Координаты</td>
				<td class="c" width="40">Место</td>
			</tr>
			<? if (count($parse['result']) > 0): ?>
				<? foreach ($parse['result'] AS $result): ?>
					<tr>
						<th><?=$result['username'] ?></th>
						<th nowrap>
							<a href="javascript:;" onclick="showWindow('<?=$result['username'] ?>: отправить сообщение', '<?=$this->url->get('messages/write/'.$result['id'].'/') ?>', 680)" title="Написать сообщение"><span class='sprite skin_m'></span></a>
							<a href="<?=$this->url->get('buddy/new/'.$result['id'].'/') ?>" title="Предложение подружиться"><span class='sprite skin_b'></span></a>
						</th>
						<th><? if ($result['race'] != 0): ?><img src="<?=$this->url->getBaseUri() ?>assets/images/skin/race<?=$result['race'] ?>.gif" width="16" height="16"><? else: ?>&nbsp;<? endif; ?>
						</th>
						<th><?=$result['ally_name'] ?></th>
						<th><?=$result['planet_name'] ?></th>
						<th><a href="<?=$this->url->get('galaxy/'.$result['g'].'/'.$result['s'].'/') ?>"><?=$result['g'] ?>:<?=$result['s'] ?>:<?=$result['p'] ?></a></th>
						<th><a href="<?=$this->url->get('stat/players/range/'.$result['total_rank'].'/') ?>"><?=$result['total_rank'] ?></a></th>
					</tr>
				<? endforeach; ?>
			<? else: ?>
				<tr>
					<th colspan="7">Поиск не дал результатов</th>
				</tr>
			<? endif; ?>
		</table>
	<? else: ?>

		<table class="table">
			<tr>
				<td class="c">Аббревиатура</td>
				<td class="c">Имя</td>
				<td class="c">Члены</td>
				<td class="c">Очки</td>
			</tr>
			<? if (count($parse['result']) > 0): ?>
				<? foreach ($parse['result'] AS $result): ?>
					<tr>
						<th><a href="<?=$this->url->get('alliance/info/'.$result['id'].'/') ?>"><?=$result['tag'] ?></a></th>
						<th><?=$result['name'] ?></th>
						<th><?=$result['members'] ?></th>
						<th><?=$result['total_points'] ?></th>
					</tr>
				<? endforeach; ?>
			<? else: ?>
				<tr>
					<th colspan="6">Поиск не дал результатов</th>
				</tr>
			<? endif; ?>
		</table>

	<? endif; ?>

<? endif; ?>