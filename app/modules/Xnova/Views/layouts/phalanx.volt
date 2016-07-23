<?
/**
 * @var $list array
 */
?>
<table class="table">
	<tr>
		<td class="c" colspan="7">Обнаружена следующая активность на планете:
		</td>
	</tr>
	<? if (!count($list)): ?>
		<tr>
			<th>На этой планете нет движения флотов.</th>
		</tr>
	<? else: ?>
		<? foreach ($list as $item): ?>
			<?=$item ?>
		<? endforeach; ?>
	<? endif; ?>
</table>