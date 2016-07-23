<?
/**
 * @var $parse array
 */
?>
<div class="table-responsive">
	<table class="table table-striped table-hover table-advance">
		<thead>
			<tr>
				<th>Название</th>
				<th>Позиция</th>
				<th width="150">Активность</th>
			</tr>
		</thead>
		<? foreach ($parse['rows'] AS $planet): ?>
			<tr>
				<td><?=$planet['name'] ?></td>
				<td><?=$planet['position'] ?></td>
				<td><?=\Xnova\Helpers::pretty_time($planet['activity']) ?></td>
			</tr>
		<? endforeach; ?>
	</table>
	<div class="row">
		<div class="col-md-5 col-sm-12">
			<div class="dataTables_info">
				Активно <?=$parse['total'] ?> планет<?=\Xnova\Helpers::morph($parse['total'], 'feminine', 5) ?>
			</div>
		</div>
		<div class="col-md-7 col-sm-12">
			<div class="dataTables_paginate paging_bootstrap">
				<?=$pagination ?>
			</div>
		</div>
	</div>
</div>