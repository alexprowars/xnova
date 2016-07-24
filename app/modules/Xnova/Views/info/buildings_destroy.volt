<div class="separator"></div>
<table class="table">
	<tr>
		<td class="c big" align="center">
			<a href="<?=$this->url->get('buildings/index/cmd/destroy/building/'.$parse['image'].'/') ?>">Снести: <?=$parse['name'] ?> уровень <?=$parse['levelvalue'] ?> ?</a>
		</td>
	</tr>
	<tr>
		<th><?=$parse['destroy'] ?></th>
	</tr>
	<tr>
		<th><br>Время сноса: <?=$parse['destroytime'] ?><br><br></th>
	</tr>
</table>