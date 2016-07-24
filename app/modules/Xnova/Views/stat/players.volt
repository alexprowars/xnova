<div class="table">
	<div class="row">
		<div class="c col-sm-1 col-xs-2 middle">Место</div>
		<div class="c col-sm-1 hidden-xs-down middle">+/-</div>
		<div class="c col-sm-4 col-xs-5 middle">Игрок</div>
		<div class="c col-sm-1 col-xs-2 middle">&nbsp;</div>
		<div class="c col-sm-3 hidden-xs-down middle">Альянс</div>
		<div class="c col-sm-2 col-xs-3 middle">Очки</div>
	</div>
	<? foreach ($stat AS $s): ?>
		<div class="row">
			<div class="th col-sm-1 col-xs-2">
				<?=$s['rank'] ?>
				<div class="hidden-sm-up"><?=$s['rankplus'] ?></div>
			</div>
			<div class="th col-sm-1 hidden-xs-down"><?=$s['rankplus'] ?></div>
			<div class="th col-sm-4 col-xs-5">
				<a href="{{ url('players/'.$s['id'].'/') }}" class="window popup-user"><?=$s['name'] ?></a>
				<div class="hidden-sm-up">
					<?=$s['alliance'] ?>
				</div>
			</div>
			<div class="th col-sm-1 col-xs-2">
				<? if ($s['race'] != 0): ?><img src="<?=$this->url->getBaseUri() ?>assets/images/skin/race<?=$s['race'] ?>.gif" width="16" height="16" class="pull-xs-left" style="margin-left:7px;"><? endif; ?>
				<? if (isset($userId) && $userId != 0): ?><?=$s['mes'] ?><? endif; ?>
			</div>
			<div class="th col-sm-3 hidden-xs-down"><?=$s['alliance'] ?></div>
			<div class="th col-sm-2 col-xs-3 middle">
				<a href="{{ url('players/stat/'.$s['id'].'/') }}"><?=$s['points'] ?></a>
			</div>
		</div>
	<? endforeach; ?>
</div>