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

	<?php $this->assets->outputCss('css') ?>
	<?php $this->assets->outputJs('js') ?>

	<!--[if lte IE 9]>
		<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
		<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->

	<? if (!class_exists('\Xnova\Helpers') || !\Xnova\Helpers::allowMobileVersion()): ?>
		<meta name="viewport" content="width=810">
	<? else: ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script type="text/javascript">
			$(document).ready(function()
			{
				if (!navigator.userAgent.match(/(\(iPod|\(iPhone|\(iPad)/))
				{
					$("body").swipe(
					{
						swipeLeft: function()
						{
							if ($('.menu-sidebar').hasClass('opened'))
								$('.menu-toggle').click();
							else
								$('.planet-toggle').click();
						},
						swipeRight: function()
						{
							if ($('.planet-sidebar').hasClass('opened'))
								$('.planet-toggle').click();
							else
								$('.menu-toggle').click();
						},
						threshold: 100,
						excludedElements: ".table-responsive",
						fallbackToMouseEvents: false,
						allowPageScroll: "auto"
					});
				}
			});
		</script>
	<? endif; ?>
</head>
<body class="<? if ($this->config->view->get('socialIframeView', 0) == 1): ?>iframe<? else: ?>window<? endif; ?>">
	<script type="text/javascript">
		XNova.path = '<?=$this->url->getBaseUri() ?>';
		timestamp = <?=time() ?>;
		timezone = <?=$timezone ?>;
		ajax_nav = <?=$ajaxNavigation ?>;
		addToUrl = '<? if (!$this->cookies->has($this->config->cookie->prefix.'_full') && $this->session->has('OKAPI')): ?><?=http_build_query($this->session->get('OKAPI')) ?><? endif; ?>';

		<? if ($this->auth->isAuthorized()): ?>
			XNova.fleetSpeed 	= <?=$this->game->getSpeed('fleet') ?>;
			XNova.gameSpeed 	= <?=round($this->config->game->get('game_speed', 1) / 2500, 1) ?>;
			XNova.resSpeed 		= <?=$this->config->game->get('resource_multiplier', 1) ?>;
		<? endif; ?>
	</script>

	<div id="box" class="set_<?=$controller ?>">
		<? if (isset($leftMenu) && $leftMenu == true): ?>
			<? $this->partial('shared/header'); ?>
		<? endif; ?>

		<div class="game_content">
			<? if (isset($leftMenu) && $leftMenu == true): ?>
				<? $this->partial('shared/menu'); ?>
			<? endif; ?>

			<? if (isset($leftMenu) && $leftMenu == true): ?>
				<? $this->partial('shared/planets'); ?>
			<? endif; ?>

			<? if ($this->config->view->get('socialIframeView', 0) == 1): ?><div class="iframe_wrapper"><? endif; ?>
			<div id="gamediv" class="content">

				<? if (isset($topPanel) && $topPanel == true): ?>
					<? $this->partial('shared/panel'); ?>
				<? endif; ?>

				<? if (isset($deleteUserTimer) && $deleteUserTimer > 0): ?>
					<table class="table"><tr><td class="c" align="center">Включен режим удаления профиля!<br>Ваш аккаунт будет удалён после <?=$this->game->datezone("d.m.Y", $deleteUserTimer) ?> в <?=$this->game->datezone("H:i:s", $deleteUserTimer) ?>. Выключить режим удаления можно в настройках игры.</td></tr></table><div class="separator"></div>
				<? endif; ?>

				<? if (isset($vocationTimer) && $vocationTimer > 0): ?>
				   <table class="table"><tr><td class="c negative" align="center">Включен режим отпуска! Функциональность игры ограничена.</td></tr></table><div class="separator"></div>
				<? endif; ?>

				<? if (isset($globalMessage) && $globalMessage != ''): ?>
				   <table class="table"><tr><td class="c" align="center"><?=$globalMessage ?></td></tr></table><div class="separator"></div>
				<? endif; ?>

				<div class="content-row">
					<?

					$messages = $this->flashSession->getMessages();

					if (count($messages))
					{
						foreach ($messages as $type => $items)
						{
							foreach ($items as $message)
							{
								if ($type == 'alert')
									echo '<script>$(document).ready(function(){alert("'.$message.'");});</script>';
								else
								{
									echo $message;
								}
							}
						}
					}

					?>
					<?php echo $this->getContent() ?>
				</div>

			</div>
			<? if ($this->config->view->get('socialIframeView', 0) == 1): ?></div><? endif; ?>
		</div>

		<? if (isset($leftMenu) && $leftMenu == true): ?>
			<footer class="hidden-xs-down">
				<div class="container-fluid">
					<div class="pull-xs-left text-xs-left">
						<a href="<?=$this->url->get('news/') ?>" title="Последние изменения"><?=VERSION ?></a>
						<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
							<a class="hidden-sm-down" target="_blank" href="http://xnova.su/">© 2008 - <?=date("Y") ?> Xcms</a>
						<? endif; ?>
					</div>
					<div class="pull-xs-right text-xs-right">
						<? if ($this->config->view->get('socialIframeView', 0) == 1): ?>
							<a href="http://www.odnoklassniki.ru/group/56711983595558" class="ok" target="_blank">Группа игры</a>|
						<? endif; ?>
						<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
						<a href="<?=$this->url->get('banned/') ?>">Тёмные</a>|
						<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
							<a href="//vk.com/xnova_game" target="_blank">ВК</a>|
							<a href="<?=$this->url->get('contact/') ?>">Контакты</a>|
						<? endif;?>
						<a href="<?=$this->url->get('content/help/') ?>">Новичкам</a>|
						<a href="<?=$this->url->get('content/agb/') ?>">Правила</a>|
						<a onclick="" title="Игроков в сети" style="color:green"><?=$this->config->app->get('users_online', 0) ?></a>/<a onclick="" title="Всего игроков" style="color:yellow"><?=$this->config->app->get('users_total', 0) ?></a>
					</div>
					<div class="clearfix"></div>
			</footer>
			<div class="row hidden-sm-up footer-mobile">
				<div class="col-xs-12 text-xs-center">
					<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
					<a href="<?=$this->url->get('banned/') ?>">Тёмные</a>|
					<a href="<?=$this->url->get('contact/') ?>">Контакты</a>|
					<a href="<?=$this->url->get('content/help/') ?>">Новичкам</a>|
					<a href="<?=$this->url->get('content/agb/') ?>">Правила</a>
				</div>
				<div class="col-xs-8 text-xs-center">
					<a href="<?=$this->url->get('news/') ?>" title="Последние изменения"><?=VERSION ?></a>
					<? if ($this->config->view->get('socialIframeView', 0) == 0): ?>
						<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - <?=date("Y") ?> Xcms</a>
					<? endif; ?>
				</div>
				<div class="col-xs-4 text-xs-center">
					<a onclick="" title="Игроков в сети" style="color:green"><?=$this->config->app->get('users_online', 0) ?></a>/<a onclick="" title="Всего игроков" style="color:yellow"><?=$this->config->app->get('users_total', 0) ?></a>
				</div>
			</div>
		<? endif; ?>
	</div>

	<? $this->partial('shared/socials'); ?>

	<div id="windowDialog"></div>
	<div id="tooltip" class="tip"></div>
</body>
</html>