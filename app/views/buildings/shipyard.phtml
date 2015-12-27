<div class="block">
	<div class="content">
		<form action="?set=buildings&mode=<?=$parse['mode'] ?>" method="post">
			<div class="row shipyard">
				<div class="col-xs-12 c">
					<input type="submit" value="Построить">
				</div>
				<div class="clearfix"></div>
				<? foreach ($parse['buildlist'] AS $build): ?>
					<div class="col-md-6">
						<div class="viewport buildings <? if (!$build['access']): ?>shadow<? endif; ?>">
							<? if (!$build['access']): ?>
								<div class="notAvailable tooltip" data-tooltip-content="Требования:<br><?=str_replace('"', '\'', getTechTree($build['i'])) ?>" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=$build['i'] ?>&ajax&popup', 600)"><span>недоступно</span></div>
							<? endif; ?>

							<div class="img">
								<a href="javascript:;" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=$build['i'] ?>&ajax&popup', 600)">
									<img src="<?=RPATH ?><?=DPATH ?>gebaeude/<?=$build['i'] ?>.gif" alt='<?=_getText('tech', $build['i']) ?>' align=top width=120 height=120 class="tooltip" data-tooltip-content='<center><?=_getText('descriptions', $build['i']) ?></center>' data-tooltip-width="150">
								</a>

								<div class="overContent">
									<?=$build['price'] ?>
								</div>
							</div>
							<div class="title">
								<a href=?set=infos&gid=<?=$build['i'] ?>><?=_getText('tech', $build['i']) ?></a> (<span class="<?=($build['count'] > 0 ? 'positive' : 'negative') ?>"><?=\Xcms\Strings::pretty_number($build['count']) ?></span>)
							</div>
							<div class="actions">
								<? if ($build['access']): ?>
									Время: <?=\Xcms\Strings::pretty_time($build['time']); ?>
									<? if ($build['add'] != ''): ?>
										<?=$build['add'] ?>
									<? else: ?>
										<br>
									<? endif; ?>
									<? if ($build['can_build']): ?>
										<? if ($build['maximum']): ?>
											<br>
											<center><font color="red">Вы можете построить только <?=$build['max'] ?> постройку данного типа</font></center>
										<? else: ?>
											<br>
											<a href=javascript:setMaximum(<?=$build['i'] ?>,<?=$build['max']?>);>Максимум: <font color="lime"><?=$build['max']?></font></a>
											<div class="buildmax">
												<input type=text name=fmenge[<?=$build['i'] ?>] alt='<?=_getText('tech', $build['i']) ?>' size="7" maxlength="5" value="" placeholder="0">
											</div>
										<? endif; ?>
									<? endif; ?>
								<? endif; ?>
							</div>
						</div>
					</div>
				<? endforeach; ?>
				<div class="clearfix"></div>
				<div class="col-xs-12 c">
					<input type="submit" value="Построить">
				</div>
			</div>
		</form>
	</div>
</div>