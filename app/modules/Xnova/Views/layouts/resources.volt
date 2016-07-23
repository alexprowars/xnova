<?
/**
 * @var $parse array
 */
?>
<form action="<?=$this->url->get('resources/') ?>" method="post">
	<table width="100%">
		<tr>
			<td class="c" align="center">Уровень производства</td>
			<th><?=$parse['production_level'] ?></th>
			<th width="40%">
				<div style="border: 1px solid #9999FF;">
					<div id="prodBar" style="background-color: <?=$parse['production_level_barcolor'] ?>; width: <?=$parse['production_level_bar'] ?>%;">
						&nbsp;
					</div>
				</div>
			</th>
		</tr>
		<tr>
			<td class="c" align="center"><a href="<?=$this->url->get('info/113/') ?>">Энергетическая технология</a></td>
			<th><?=$parse['et'] ?> ур.</th>
		</tr>
	</table>
	<div class="separator"></div>
	<table width="100%">
		<tr>
			<td class="c" colspan="5">Управление шахтами и энергетикой</td>
		</tr>
		<tr>
			<th width="50%"><a href="<?=$this->url->get('resources/production/active/Y/') ?>" class="button">Включить на всех<br>планетах</a></th>
			<th><a href="<?=$this->url->get('resources/production/active/N/') ?>" class="button">Выключить на всех<br>планетах</a></th>
		</tr>
	</table>
	<div class="separator"></div>
	<div class="table-responsive">
		<table width="100%">
			<tr>
				<td class="c" colspan="8">Производство на планете <?=$parse['name'] ?></td>
			</tr>
			<tr>
				<th width="200"></th>
				<th>Ур.</th>
				<th>Бонус</th>
				<th><a href="javascript:" onclick="showWindow('<?=_getText('tech', 1) ?>', '<?=$this->url->get('info/1/') ?>', 600)">Металл</a></th>
				<th><a href="javascript:" onclick="showWindow('<?=_getText('tech', 2) ?>', '<?=$this->url->get('info/2/') ?>', 600)">Кристалл</a></th>
				<th><a href="javascript:" onclick="showWindow('<?=_getText('tech', 3) ?>', '<?=$this->url->get('info/3/') ?>', 600)">Дейтерий</a></th>
				<th><a href="javascript:" onclick="showWindow('<?=_getText('tech', 4) ?>', '<?=$this->url->get('info/4/') ?>', 600)">Энергия</a></th>
				<th width="100">КПД</th>
			</tr>
			<tr>
				<th class="text-xs-left" nowrap>Базовое производство</th>
				<td class="k">-</td>
				<td class="k">-</td>
				<td class="k"><?=$parse['metal_basic_income'] ?></td>
				<td class="k"><?=$parse['crystal_basic_income'] ?></td>
				<td class="k"><?=$parse['deuterium_basic_income'] ?></td>
				<td class="k"><?=$parse['energy_basic_income'] ?></td>
				<td class="k">100%</td>
			</tr>
			<? foreach ($parse['resource_row'] as $resource): ?>
				<tr>
					<th class="text-xs-left" nowrap><a href="javascript:" onclick="showWindow('<?=_getText('tech', $resource['id']) ?>', '<?=$this->url->get('info/'.$resource['id'].'/') ?>', 600)"><?=_getText('tech', $resource['id']) ?></a></th>
					<th><font color="#ffffff"><?=$resource['level_type'] ?></font></th>
					<th><font color="#ffffff"><?=$resource['bonus'] ?>%</font></th>
					<? foreach ($this->storage->reslist['res'] AS $res): ?>
						<th><font color="#ffffff"><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($resource[$res.'_type'])) ?></font></th>
					<? endforeach; ?>
					<th><font color="#ffffff"><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($resource['energy_type'])) ?></font></th>
					<th>
						<select name="<?=$resource['name'] ?>" title="">
						<? for ($j = 10; $j >= 0; $j--): ?>
							<option value="<?=$j ?>"<?=($j == $resource['porcent'] ? ' selected=selected' : '') ?>><?=($j * 10) ?>%</option>
						<? endfor; ?>
						</select>
					</th>
				</tr>
			<? endforeach; ?>
			<tr>
			</tr>
			<tr>
				<th colspan="2">Вместимость:</th>
				<th><?=$parse['bonus_h'] ?>%</th>
				<? foreach ($this->storage->reslist['res'] AS $res): ?>
					<td class="k"><?=$parse[$res.'_max'] ?></td>
				<? endforeach; ?>
				<td class="k"><font color="#00ff00"><?=$parse['energy_max'] ?></font></td>
				<td class="k"><input name="action" value="Пересчитать" type="submit"></td>
			</tr>
			<tr>
				<th colspan="3">Сумма:</th>
				<? foreach ($this->storage->reslist['res'] AS $res): ?>
					<td class="k"><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($parse[$res.'_total'])) ?></td>
				<? endforeach; ?>
				<td class="k"><?=$parse['energy_total'] ?></td>
			</tr>
		</table>
	</div>
	<div class="separator"></div>
	<table width="100%">
		<tr>
			<td class="c" colspan="5">Информация о производстве</td>
		</tr>
		<tr>
			<th width="16%">&nbsp;</th>
			<th width="21%">Час</th>
			<th width="21%">День</th>
			<th width="21%">Неделя</th>
			<th width="21%">Месяц</th>
		</tr>
		<? foreach ($this->storage->reslist['res'] AS $res): ?>
			<tr>
				<th><?=_getText('res', $res) ?></th>
				<th><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($parse[$res.'_total'])) ?></th>
				<th><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($parse[$res.'_total'] * 24)) ?></th>
				<th><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($parse[$res.'_total'] * 24 * 7)) ?></th>
				<th><?=\App\Helpers::colorNumber(\App\Helpers::pretty_number($parse[$res.'_total'] * 24 * 30)) ?></th>
			</tr>
		<? endforeach; ?>
	</table>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="3">Статус хранилища</td>
		</tr>
		<? foreach ($this->storage->reslist['res'] AS $res): ?>
			<tr>
				<th width="150"><?=_getText('res', $res) ?></th>
				<th width="100"><?=$parse[$res.'_storage'] ?>%</th>
				<th>
					<div style="border: 1px solid #9999FF;">
						<div id="AlmMBar" style="background-color: <?=$parse[$res.'_storage_barcolor'] ?>; width: <?=min(100, max(0, $parse[$res.'_storage_bar'])) ?>%;">
							&nbsp;
						</div>
					</div>
				</th>
			</tr>
		<? endforeach; ?>
	</table>
</form>
<? if ($parse['buy_form']): ?>
	<div class="separator"></div>
	<table class="table">
		<tr>
			<td class="c" colspan="5">Покупка ресурсов (8 ч. выработка ресурсов)</td>
		</tr>
		<tr>
			<th width="30%">
				<? if ($parse['merchand'] < time()): ?>
					<a href="<?=$this->url->get('resources&buy=1/') ?>" class="button">Купить за 10 кредитов</a>
				<? else: ?>
					Через <?= \App\Helpers::pretty_time($parse['merchand'] - time()) ?>
				<? endif; ?>
			</th>
			<th>Вы можете купить: <?=$parse['buy_metal'] ?> металла, <?=$parse['buy_crystal'] ?> кристалла, <?=$parse['buy_deuterium'] ?> дейтерия</th>
		</tr>
	</table>
<? endif; ?>