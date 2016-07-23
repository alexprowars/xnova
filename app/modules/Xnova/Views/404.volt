<?= $this->tag->getDoctype() ?>
<html lang="ru">
<head>
	<?php echo $this->tag->getTitle(); ?>
	<?= $this->tag->tagHtml('meta', ['name' => 'description', 'content' => '']) ?>
	<?= $this->tag->tagHtml('meta', ['name' => 'keywords', 'content' => '']) ?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="image_src" href="//<?=$_SERVER['HTTP_HOST'] ?><?=$this->url->getBaseUri() ?>assets/images/logo.jpg" />
	<link rel="apple-touch-icon" href="//<?=$_SERVER['HTTP_HOST'] ?><?=$this->url->getBaseUri() ?>assets/images/apple-touch-icon.png"/>

	<?php $this->assets->outputJs('js') ?>
	<?php $this->assets->outputCss('css') ?>
</head>
<body class="<? if ($this->config->view->get('socialIframeView', 0) == 1): ?>iframe<? else: ?>window<? endif; ?>">
	<div id="box" class="set_error">
		<? if (isset($leftMenu) && $leftMenu == true): ?>
			<? $this->partial('shared/header'); ?>
		<? endif; ?>
		<div class="game_content">
			<? if (isset($leftMenu) && $leftMenu == true): ?>
				<? $this->partial('shared/menu'); ?>
			<? endif; ?>
			<div id="gamediv" class="content container-fluid">
				<div class="row">

					<div class="e404">Вы попали на несуществующую страницу!</div>

					<div id="gamecontainer" style="margin: 0 auto">
						<canvas id="gameCanvas"></canvas>
					</div>

					<script src="<?=$this->url->get('assets/404/js/spaceinvaders.js') ?>"></script>
					<script>
						//  Setup the canvas.
						var canvas = document.getElementById("gameCanvas");
						canvas.width = 600;
						canvas.height = 500;

						var baseUri = '<?=$this->url->getBaseUri() ?>';

						//  Create the game.
						var game = new Game();

						//  Initialise it with the game canvas.
						game.initialise(canvas);

						//  Start the game.
						game.start();

						//  Listen for keyboard events.
						window.addEventListener("keydown", function keydown(e)
						{
							var keycode = e.which || window.event.keycode;
							//  Supress further processing of left/right/space (37/29/32)
							if (keycode == 37 || keycode == 39 || keycode == 32)
							{
								e.preventDefault();
							}
							game.keyDown(keycode);
						});
						window.addEventListener("keyup", function keydown(e)
						{
							var keycode = e.which || window.event.keycode;
							game.keyUp(keycode);
						});

						function toggleMute()
						{
							game.mute();
							document.getElementById("muteLink").innerText = game.sounds.mute ? "Со звуком" : "Без звука";
						}
					</script>
					<br>
					<div><a id="muteLink" href="#" onclick="toggleMute()">Без звука</a></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>