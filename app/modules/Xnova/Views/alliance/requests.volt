<table class="table">
	<tr>
		<td class="c" colspan="2">Обзор заявок [<?=$parse['tag'] ?>]</td>
	</tr>
	<? if (is_array($parse['request'])): ?>
		<tr><td colspan="2">
		<form action="{{ url('alliance/admin/edit/requests/show/'.$parse['request']['id'].'/sort/0/') }}" method="POST">
			<table width="100%">
			<tr>
				<th colspan="2">Заявка от <?=$parse['request']['username'] ?></th>
			</tr>
			<tr>
				<th colspan="2"><?=$parse['request']['request_text'] ?></th>
			</tr>
			<tr>
				<td class="c" colspan="2">Форма ответа:</td>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" name="action" value="Принять"></th>
			</tr>
			<tr>
				<th colspan="2"><textarea name="text" cols=40 rows=10 title=""></textarea></th>
			</tr>
			<tr>
				<th colspan="2"><input type="submit" name="action" value="Отклонить"></th>
			</tr>
			</table>
		</form>
		</td></tr>
	<? endif; ?>
	<? if (count($parse['list']) > 0): ?>
		<tr>
			<td class="c text-xs-center">
				<a href="{{ url('alliance/admin/edit/requests/show/0/sort/1/') }}">Логин</a>
			</td>
			<td class="c text-xs-center">
				<a href="{{ url('alliance/admin/edit/requests/show/0/sort/0/') }}">Дата подачи заявки</a>
			</td>
		</tr>
		<? foreach ($parse['list'] AS $list): ?>
			<tr>
				<th class="text-xs-center">
					<a href="{{ url('alliance/admin/edit/requests/show/'.$list['id'].'/sort/0/') }}"><?=$list['username'] ?></a>
				</th>
				<th class="text-xs-center">
					<?=$list['time'] ?>
				</th>
			</tr>
		<? endforeach; ?>
	<? else: ?>
		<tr>
			<th colspan="2">Список заявок пуст</th>
		</tr>
	<? endif; ?>
	<tr>
		<td class="c" colspan="2"><a href="{{ url('alliance/') }}">Назад</a></td>
	</tr>
</table>