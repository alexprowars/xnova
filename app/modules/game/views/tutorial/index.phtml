<?
/**
 * @var $parse array
 */
?>
<table class="table">
	<tr>
		<td class="c" colspan="3">Текущие задания</td>
	</tr>
	<? foreach ($parse['list'] AS $quest): ?>
		<tr>
			<th width="30"><?=$quest['ID'] ?></th>
			<th width="30"><img src="<?=$this->url->getBaseUri() ?>assets/images/<?=($quest['FINISH'] ? 'check' : 'none') ?>.gif" height="11" width="12"></th>
			<th class="text-xs-left">
				<? if ($quest['AVAILABLE']): ?>
					<a href="<?=$this->url->get('tutorial/'.$quest['ID'].'/') ?>"><span class="positive"><?=$quest['TITLE'] ?></span></a>
				<? else: ?>
					<span class="positive"><?=$quest['TITLE'] ?></span>
				<? endif; ?>
				<? if (!$quest['AVAILABLE'] && isset($quest['REQUIRED']) && count($quest['REQUIRED'])): ?>
					<br><br>Требования:
					<? foreach ($quest['REQUIRED'] AS $key => $req): ?>
						<br>
						<? if ($key == 'QUEST'): ?>
							<span class="<?=((!isset($parse['quests'][$req]) || (isset($parse['quests'][$req]) && $parse['quests'][$req]['finish'] == 0)) ? 'negative' : 'positive') ?>">Выполнение задания №<?=$req ?></span>
						<? elseif ($key == 'LEVEL_MINIER'): ?>
							<span class="<?=($this->user->lvl_minier < $req ? 'negative' : 'positive') ?>">Промышленный уровень <?=$req ?></span>
						<? elseif ($key == 'LEVEL_RAID'): ?>
							<span class="<?=($this->user->lvl_raid < $req ? 'negative' : 'positive') ?>">Военный уровень <?=$req ?></span>
						<? endif; ?>
					<? endforeach; ?>
				<? endif; ?>
			</th>
		</tr>
	<? endforeach; ?>
</table>