<div class="block">
	<div class="title">
		Занято полей
		<font color="#00FF00"><?=$parse['planet_field_current'] ?></font> из <font color="#FF0000"><?=$parse['planet_field_max'] ?></font>
		<div class="pull-right col-xs-12 col-sm-6 nopadding">Осталось <span class="positive"><?=$parse['field_libre'] ?></span> свободн<?=\Xcms\Strings::morph($parse['field_libre'], 'neuter', 2) ?> пол<?=\Xcms\Strings::morph($parse['field_libre'], 'neuter', 1) ?></div>
		<div class="clearfix"></div>
	</div>
	<? foreach ($parse['BuildList'] AS $list): ?>
		<table class="table" id="building">
			<tr>
				<td class="c" width="50%">
					<?=$list['ListID'] ?>: <?=$list['ElementTitle'] ?> <?=$list['BuildLevel'] ?><? if ($list['BuildMode'] != 'build'): ?>. <?=_getText('destroy') ?><? endif; ?>
				</td>
				<td class="k">
					<? if ($list['ListID'] == 1): ?>
						<div id="blc" class="z"></div>
						<script type="text/javascript">BuildTimeout(<?=$list['BuildTime'] ?>, <?=$list['ListID'] ?>, <?=$list['PlanetID'] ?>, <?=(isset($_SESSION['LAST_ACTION_TIME']) ? $_SESSION['LAST_ACTION_TIME'] : 0) ?>);</script>
						<div class="positive"><?=datezone("d.m H:i:s", $list['BuildEndTime']) ?></div>
					<? else: ?>
						<a href="?set=buildings&listid=<?=$list['ListID'] ?>&cmd=remove&planet=<?=$list['PlanetID'] ?>">Удалить</a>
					<? endif; ?>
				</td>
			</tr>
		</table>
	<? endforeach; ?>
	<div class="content">
		<div id="tabs" class="ui-tabs ui-widget ui-widget-content">
			<div class="head hidden-xs">
				<ul class="ui-tabs-nav ui-widget-header">
					<li id="tab_area"><a href="#tabs-0">Планета</a></li>
					<li id="tab_list"><a href="#tabs-1">Постройки</a></li>
				</ul>
			</div>
			<div id="tabs-0" class="ui-tabs-panel ui-widget-content">
				<div class="buildings area <?=$parse['planettype'] ?>">
					<? foreach ($parse['BuildingsList'] AS $build): if (!$build['access']) continue; ?>
						<div data-id="<?=$build['i'] ?>" class="object i_<?=$build['i'] ?> <?=($build['count'] <= 0 ? 'empty' : '') ?> tooltip" data-tooltip-content='<center><?=_getText('descriptions', $build['i']) ?></center>' data-tooltip-width="150">
							<img src="<?=RPATH ?>images/buildings/<?=$build['i'] ?>.png" alt="<?=_getText('tech', $build['i']) ?>">
							<div class="name"><?=_getText('tech', $build['i']) ?> <span><?=\Xcms\Strings::pretty_number($build['count']) ?></span></div>
						</div>
					<? endforeach; ?>
				</div>
			</div>
			<div id="tabs-1" class="ui-tabs-panel ui-widget-content" style="display: none">
				<div class="row" id="building">
					<? $i = 0; foreach ($parse['BuildingsList'] AS $build): $i++; ?>
					<div class="col-md-6" id="object_<?=$build['i'] ?>">
						<div class="viewport buildings <? if (!$build['access']): ?>shadow<? endif; ?>">
							<? if (!$build['access']): ?>
								<div class="notAvailable tooltip" data-tooltip-content="Требования:<br><?=str_replace('"', '\'', getTechTree($build['i'])) ?>" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=$build['i'] ?>&ajax&popup', 600, 500)"><span>недоступно</span></div>
							<? endif; ?>

							<div class="img">
								<a href="javascript:;" onclick="showWindow('<?=_getText('tech', $build['i']) ?>', '?set=infos&gid=<?=$build['i'] ?>&ajax&popup', 600)">
									<img src="<?=RPATH ?><?=DPATH ?>gebaeude/<?=$build['i'] ?>.gif" align="top" alt="" class="tooltip img-responsive" data-tooltip-content='<center><?=_getText('descriptions', $build['i']) ?></center>' data-tooltip-width="150">
								</a>

								<div class="overContent">
									<?=$build['price'] ?>
								</div>
							</div>
							<div class="title">
								<a href=?set=infos&gid=<?=$build['i'] ?>><?=_getText('tech', $build['i']) ?></a>
							</div>
							<div class="actions">
								Уровень: <span class="<?=($build['count'] > 0 ? 'positive' : 'negative') ?>"><?=\Xcms\Strings::pretty_number($build['count']) ?></span><br>
								<? if ($build['access']): ?>
									Время: <?=\Xcms\Strings::pretty_time($build['time']); ?><br>
									<?=$build['add'] ?>
									<div class="startBuild"><?=$build['click'] ?></div>
								<? endif; ?>
							</div>
						</div>
					</div>
					<? endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function()
	{
		var tab = 0;
	
		if (html5_storage())
		{
			tab = parseInt(localStorage.getItem("buildingAreaTab"));
			
			if (isNaN(tab))
				tab = 0;
		}
		
		if (!$('#tab_area').is(':visible'))
			tab = 1;
		
		$( "#tabs" ).tabs({active: tab, activate: function( event, ui ) 
		{
			var t = $(ui.newPanel).attr('id').split('-');
			
			localStorage.setItem("buildingAreaTab", parseInt(t[1]));
		}});

		$('#building').on('click', '#blc', function(e)
		{
			$(this).remove();
		});

		$('.buildings.area .object').on('click', function(e)
		{
			var id = $(this).data('id');

			$('#windowDialog')
					.dialog("option", "title", 'Информация о постройке')
					.dialog("option", "height", 167)
					.html($('#object_'+id).html())
					.dialog("option", "position", {my: "center", at: "center", of: window})
					.dialog("open");
		});
	});
</script>