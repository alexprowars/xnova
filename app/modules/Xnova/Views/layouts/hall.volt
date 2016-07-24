<table class="table">
	<tr>
		<td class="c" width="100">TOP50</td>
		<td class="c"><a href="{{ url('hall/') }}">Зал Славы</a></td>
		<td class="c" width="137">
			<form method="POST" action="{{ url('hall/') }}" id="hall"><select name="visible" onChange="$('#hall').submit()" title="">
				<option value="1" <?=((!isset($_POST['visible']) || $_POST['visible'] <= 1) ? 'selected' : '') ?>>Бои
				<option value="2" <?=((isset($_POST['visible']) && $_POST['visible'] == 2) ? 'selected' : '') ?>>САБ
			</select></form>
		</td>
	</tr>
</table>
<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" width="35">Место</td>
			<td class="c"><font color=#CDB5CD><?=((!isset($_POST['visible']) || $_POST['visible'] <= 1) ? 'Самые разрушительные бои' : 'Самые разрушительные групповые бои') ?></font></td>
			<td class="c" width="45">Итог</td>
			<td class="c" width="125">Дата</td>
		</tr>
		<? if (count($parse['hall']) > 0): $i = 0;
		foreach ($parse['hall'] AS $log): $i++; ?>
			<tr>
				<th><?=$i ?></th>
				<th><a href="{{ url('log/'.$log['log'].'/') }}" <?=($this->config->view->get('openRaportInNewWindow', 0) ? 'target="_blank"' : '') ?>><?=$log['title'] ?></a></th>
				<th><? if ($log['won'] == 0)
					echo'Н';
				elseif ($log['won'] == 1)
					echo'А';
				else echo'О'; ?></th>
				<th nowrap><? if ($parse['time'] == $log['time']): ?><font color="green"><? endif; ?><?=$this->game->datezone("d.m.y H:i", $log['time']) ?><? if ($parse['time'] == $log['time']): ?></font><? endif; ?></th>
			</tr>
			<? endforeach; ?>
		<? else: ?>
		<tr>
			<th colspan="4">В этой вселенной еще не было крупных боев</th>
		</tr>
		<? endif; ?>
	</table>