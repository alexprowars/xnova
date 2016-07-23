<? if (count($planet['list'])): ?>
	<script type="text/javascript">
		$('.planetList .list').html('<? foreach ($planet['list'] AS $i => $item): ?><div class="planet type_<?=$item['planet_type'] ?> <?=($planet['current'] == $item['id'] ? 'current' : '') ?>"><a href="javascript:;" onclick="changePlanet(<?=$item['id'] ?>)" title="<?=$item['name'] ?>"><img src="<?=$this->url->getBaseUri() ?>assets/images/planeten/small/s_<?=$item['image'] ?>.jpg" height="40" width="40" alt="<?=$item['name'] ?>"></a><div><?=$item['name'] ?><br><?=\Xnova\Helpers::BuildPlanetAdressLink($item) ?></div><div class="clear"></div></div><? endforeach; ?>');
	</script>
<? endif; ?>