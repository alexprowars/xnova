<table class="table">
	<tr>
		<th colspan="4">Логовница</th>
	</tr>
	<tr>
		<td class="c" colspan="4">Ваши сохранённые логи</td>
	</tr>
	<tr>
		<td class="c">№</td>
		<td class="c">Название</td>
		<td class="c">Ссылка</td>
		<td class="c">Управление логом</td>
	</tr>
	<? foreach ($list as $i => $row): ?>
		<tr>
			<td class="b text-xs-center"><?=($i + 1) ?></td>
			<td class="b text-xs-center"><?=$row['title'] ?></td>
			<td class="b text-xs-center">
				<a href="/log/<?=$row['id'] ?>/" <?=($this->config->game->get('openRaportInNewWindow', 0) == 1 ? 'target="_blank"' : '') ?>>Открыть</a>
			</td>
			<td class="b text-xs-center"><a href="/log/delete/id/<?=$row['id'] ?>/">Удалить лог</a></td>
		</tr>
	<? endforeach; ?>
	<? if (!count($list)): ?>
		<tr align="center">
			<td class="b text-xs-center" colspan="4">У вас пока нет сохранённых логов.</td>
		</tr>
	<? endif; ?>
	<tr>
		<td class="c" colspan="4"><a href="{{ url('log/new/') }}">Создать новый лог боя</a></td>
	</tr>
</table>