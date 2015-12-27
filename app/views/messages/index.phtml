<form action="?set=messages" id="mes_form" method="post">
	<input name="category" value="<?=$parse['category'] ?>" type="hidden">
	<div class="block">
		<div class="title">
			Сообщения
			<select name="messcat" onChange="$('#mes_form').submit()">
				<? foreach ($parse['types'] AS $type): ?>
					<option value="<?=$type ?>" <?=($type == $parse['category'] ? 'selected' : '') ?>><?=_getText('type', $type) ?></option>
				<? endforeach; ?>
			</select>
			по
			<select name="show_by" onChange="$('#mes_form').submit()">
				<? foreach ($parse['limit'] AS $limit): ?>
					<option value="<?=$limit ?>" <?=($limit == $parse['lim'] ? 'selected' : '') ?>><?=$limit ?></option>
				<? endforeach; ?>
			</select>
			на странице
			<div style="float: right">
				<input name="deletemessages" value="Удалить отмеченные" type="submit">
			</div>
		</div>
		<div class="content noborder">
			<table class="table">
				<tr>
					<th width=50><input type="checkbox" onChange="SelectAll()" style='width:14px;'></th>
					<th width=150>Дата</th>
					<th>От</th>
					<th width=65>&nbsp;</th>
				</tr>
				<? foreach ($parse['list'] AS $list): ?>
					<tr>
						<th>
							<input name="showmes<?=$list['message_id'] ?>" type="hidden" value="1">
							<input name="delmes<?=$list['message_id'] ?>" type="checkbox" style='width:14px;'>
						</th>
						<th><?=datezone("d.m.y H:i:s", $list['message_time']) ?></th>
						<th><a href="?set=players&id=<?=$list['message_sender'] ?>" class="window popup-user"><?=$list['message_from'] ?></a></th>
						<th nowrap>
							<? if ($list['message_type'] == 1): ?>
								<a href="?set=messages&mode=write&id=<?=$list['message_sender'] ?>" title="Ответить"><span class='sprite skin_m'></span></a>
								&nbsp;<a href="?set=messages&mode=write&id=<?=$list['message_sender'] ?>&quote=<?=$list['message_id'] ?>" title='Цитировать сообщение'><span class='sprite skin_z'></span></a>
								&nbsp;<a href="javascript:;" onclick='window.confirm("Вы уверены что хотите отправить жалобу на это сообщение?") ? window.location.href="?set=messages&amp;abuse=<?=$list['message_id'] ?>" : false;' title='Отправить жалобу'><span class='sprite skin_s'></span></a>
							<? else: ?>
								&nbsp;
							<? endif; ?>
						</th>
					</tr>
					<tr>
						<td style="background-color:<?=_getText('mess_background', $list['message_type']) ?>;" colspan="4" class="b">
							<? if ($list['message_type'] == 1 && \Xnova\User::get()->getUserOption('bb_parser')): ?>
								<span id="m<?=$list['message_id'] ?>"></span>
								<script type="text/javascript">Text('<?=str_replace(array("\r\n", "\n", "\r"), '<br>', stripslashes($list['message_text'])) ?>', 'm<?=$list['message_id'] ?>');</script>
							<? else: ?>
								<?=stripslashes(nl2br($list['message_text'])) ?>
							<? endif; ?>
						</td>
					</tr>
				<? endforeach; ?>
				<? if (!count($parse['list'])): ?>
					<tr>
						<th colspan="4" align="center">нет сообщений</th>
					</tr>
				<? endif; ?>
			</table>
			<div style="float: left">
				<?=$parse['pages'] ?>
			</div>
			<div style="float: right;padding: 5px">
				<input name="deletemessages" value="Удалить отмеченные" type="submit">
			</div>
		</div>
	</div>
</form>