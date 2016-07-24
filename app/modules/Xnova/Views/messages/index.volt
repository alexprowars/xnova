<form action="<?=$this->url->get('messages/') ?>" id="mes_form" method="post">
	<input name="category" value="<?=$parse['category'] ?>" type="hidden">
	<div class="block">
		<div class="title">
			Сообщения
			<select name="messcat" onChange="$('#mes_form').submit()" title="">
				<? foreach ($parse['types'] AS $type): ?>
					<option value="<?=$type ?>" <?=($type == $parse['category'] ? 'selected' : '') ?>><?=_getText('type', $type) ?></option>
				<? endforeach; ?>
			</select>
			по
			<select name="show_by" onChange="$('#mes_form').submit()" title="">
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
					<th width="50"><input type="checkbox" class="checkAll" style='width:14px;' title=""></th>
					<th width="150">Дата</th>
					<th>От</th>
					<th width="65">&nbsp;</th>
				</tr>
				<? foreach ($page->items AS $item): ?>
					<tr>
						<th>
							<input name="delete[]" type="checkbox" value="<?=$item->id ?>" style='width:14px;' title="">
						</th>
						<th><?=$this->game->datezone("d.m.y H:i:s", $item->time) ?></th>
						<th>
							<? if ($item->sender > 0): ?>
								<a href="<?=$this->url->get('players/'.$item->sender.'/') ?>" class="window popup-user"><?=$item->from ?></a>
							<? else: ?>
								<?=$item->from ?>
							<? endif; ?>
						</th>
						<th nowrap>
							<? if ($item->type == 1): ?>
								<a href="<?=$this->url->get('messages/write/'.$item->sender.'/') ?>" title="Ответить"><span class='sprite skin_m'></span></a>
								&nbsp;<a href="<?=$this->url->get('messages/write/'.$item->sender.'/quote/'.$item->id.'/') ?>" title='Цитировать сообщение'><span class='sprite skin_z'></span></a>
								&nbsp;<a href="javascript:;" onclick='window.confirm("Вы уверены что хотите отправить жалобу на это сообщение?") ? window.location.href="<?=$this->url->get('messages/abuse/'.$item->id.'/') ?>" : false;' title='Отправить жалобу'><span class='sprite skin_s'></span></a>
							<? else: ?>
								&nbsp;
							<? endif; ?>
						</th>
					</tr>
					<tr>
						<td style="background-color:<?=_getText('mess_background', $item->type) ?>;" colspan="4" class="b">
							<? if ($item->type == 1 && $this->user->getUserOption('bb_parser')): ?>
								<span id="m<?=$item->id ?>"></span>
								<script type="text/javascript">Text('<?=str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes(str_replace('#BASEPATH#', $this->url->getBaseUri(), $item->text))) ?>', 'm<?=$item->id ?>');</script>
							<? else: ?>
								<?=stripslashes(nl2br(str_replace('#BASEPATH#', $this->url->getBaseUri(), $item->text))) ?>
							<? endif; ?>
						</td>
					</tr>
				<? endforeach; ?>
				<? if (!$page->total_items): ?>
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