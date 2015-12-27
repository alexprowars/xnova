<style>.image {
	max-width: 556px !important
}</style>
<form action="?set=alliance&mode=circular" method="post">
	<table class="table">
		<tr>
			<td class='c' colspan='4'><a href="?set=alliance&mode=circular">Обновить</a></td>
		</tr>
		<? if (count($parse['messages']) > 0): foreach ($parse['messages'] AS $m): ?>
		<tr>
			<td class="j">
				<table width="100%">
					<tr>
						<th class="a" width="130">
								<?=datezone("H:i:s", $m['timestamp']) ?><br><a href="?set=players&id=<?=$m['user_id'] ?>" target="_blank"><?=stripslashes($m['user']) ?></a>
							<a onclick="AddQuote('<?=stripslashes($m['user']) ?>', 'm<?=$m['id'] ?>')"> -> </a>
						</th>
						<th class="b">
							<? if ($parse['parser']): ?>
							<span id="m<?=$m['id'] ?>"></span>
							<? else: ?>
							<?= str_replace(array("\r\n", "\n", "\r"), '', stripslashes($m['message'])) ?>
							<? endif; ?>
						</th>
						<? if ($parse['ally_owner']): ?>
						<th class="b" width="20"><input name="showmes<?=$m['id'] ?>" type="hidden" value="1"><input name="delmes<?=$m['id'] ?>" type="checkbox"></th>
						<? endif; ?>
					</tr>
				</table>
			</td>
		</tr>
		<? endforeach;
	else: ?>
		<tr>
			<td colspan="3" class="b" align="center">В альянсе нет сообщений.</td>
		</tr>
		<? endif; ?>
		<tr>
			<th colspan="4"><?=$parse['pages'] ?></th>
		</tr>
		<? if ($parse['ally_owner'] && count($parse['messages']) > 0): ?>
		<tr>
			<th colspan="4">
				<select id="deletemessages" name="deletemessages">
					<option value="deletemarked">Удалить выделенные</option>
					<option value="deleteunmarked">Удалить не выделенные</option>
					<option value="deleteall">Удалить все</option>
				</select><input value="Удалить" type="submit"></th>
		</tr>
		<? endif; ?>
	</table>
</form>
<div class="separator"></div>
<script type="text/javascript">
	var messages = new Array(20);
	<? if (count($parse['messages']) > 0): foreach ($parse['messages'] AS $m): ?>
		messages['m<?=$m['id'] ?>'] = '<?=str_replace(array("\r\n", "\n", "\r"), '', addslashes(stripslashes($m['message']))) ?>';
	<? endforeach;  endif; ?>
	<? if ($parse['parser']): ?>
		$(document).ready(function(){ShowText()});
	<? endif; ?>
</script>
<form action="?set=alliance&mode=circular&sendmail=1" method="post">
	<table class="table">
		<tr>
			<td class="c">Отправить сообщение в чат альянса</td>
		</tr>
		<tr>
			<th class="nopadding">
				<div id="editor"></div>
				<textarea name="text" id="text" rows="10" onkeypress="if((event.ctrlKey) && ((event.keyCode==10)||(event.keyCode==13))) submit()"></textarea>
			</th>
		</tr>
		<tr>
			<td class="c">
				<input type="reset" value="Очистить">
				<input type="submit" value="Отправить">
			</td>
		</tr>
	</table>
	<div id="showpanel" style="display:none">
		<table class="table">
			<tr>
				<td class="c"><b>Предварительный просмотр</b></td>
			</tr>
			<tr>
				<td class="b"><span id="showbox"></span></td>
			</tr>
		</table>
	</div>
</form>
<span style="float:left;margin-left:10px;margin-top:7px;"><a href="?set=alliance">[назад к альянсу]</a></span>
<script type="text/javascript">edToolbar('text');</script>