<? if ($parse['noresearch']): ?><font color="#ff0000"><?=$parse['noresearch'] ?></font><? endif; ?>
<div class="block">
	<div class="title">Исследования</div>
	<div class="content">
		<div class="row research">
			<? foreach ($parse['technolist'] AS $build): ?>
				<div class="col-md-6">
					<div class="viewport buildings <? if (!$build['access']): ?>shadow<? endif; ?>">
						<? if (!$build['access']): ?>
							<div class="notAvailable tooltip" data-tooltip-content="Требования:<br><?=str_replace('"', '\'', getTechTree($build['i'])) ?>" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=(($build['i'] > 300) ? ($build['i'] < 350 ? ($build['i'] - 100) : ($build['i'] + 50)) : $build['i']) ?>&ajax&popup', 600)"><span>недоступно</span></div>
						<? endif; ?>

						<div class="img">
							<a href="javascript:;" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=(($build['i'] > 300) ? ($build['i'] < 350 ? ($build['i'] - 100) : ($build['i'] + 50)) : $build['i']) ?>&ajax&popup', 600)">
								<img src="<?=RPATH ?><?=DPATH ?>gebaeude/<?=(($build['i'] > 300) ? ($build['i'] < 350 ? ($build['i'] - 100) : ($build['i'] + 50)) : $build['i']) ?>.gif" align="top" width="120" height="120" class="tooltip" data-tooltip-content='<center><?=_getText('descriptions', $build['i']) ?></center>' data-tooltip-width="150">
							</a>

							<div class="overContent">
								<?=$build['tech_price'] ?>
							</div>
						</div>
						<div class="title">
							<a href=?set=infos&gid=<?=(($build['i'] > 300) ? ($build['i'] < 350 ? ($build['i'] - 100) : ($build['i'] + 50)) : $build['i']) ?>><?=_getText('tech', $build['i']) ?></a>
						</div>
						<div class="actions">
							Уровень: <?=$build['tech_level'] ?><br>

							<? if ($build['access']): ?>
								Время: <?=\Xcms\Strings::pretty_time($build['search_time']); ?>

								<? if (isset($build['add'])): ?>
								<br><br>Бонусы:<br><?= $build['add'] ?>
								<? endif; ?>
								<div class="startBuild">
									<? if (is_array($build['tech_link'])): ?>
									<div id="brp" class="z"></div>
									<script type="text/javascript">
										v = new Date();
										var brp = $('#brp');
										function t()
										{
											n = new Date();
											ss = <?=$build['tech_link']['tech_time'] ?>;
											s = ss - Math.round((n.getTime() - v.getTime()) / 1000);
											m = 0;
											h = 0;
											if (s < 0)
												brp.html('<a href="javascript:;" onclick="load(\'?set=buildings&mode=<?=$parse['mode'] ?>&cp=<?=$build['tech_link']['tech_home'] ?>\')">завершено. продолжить...</a>');
											else
											{
												if (s > 59)
												{
													m = Math.floor(s / 60);
													s = s - m * 60;
												}
												if (m > 59)
												{
													h = Math.floor(m / 60);
													m = m - h * 60;
												}
												if (s < 10)
													s = "0" + s
												if (m < 10)
													m = "0" + m

												brp.html(h + ':' + m + ':' + s + '&nbsp;<a href="javascript:;" onclick="load(\'?set=buildings&mode=<?=$parse['mode'] ?>&cmd=cancel&tech=<?=$build['tech_link']['tech_id'] ?>\')">Отменить<?=$build['tech_link']['tech_name'] ?></a>');

												window.setTimeout("t();", 999);
											}
										}
										$(document).ready(t);
									</script>
									<? else: ?>
									<?= $build['tech_link'] ?>
									<? endif; ?>
								</div>
							<? endif; ?>
						</div>
					</div>
				</div>
			<? endforeach; ?>
		</div>
	</div>
</div>