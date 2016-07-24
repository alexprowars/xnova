{{ getDoctype() }}
<html lang="ru">
<head>
	{{ getTitle() }}
	{{ tag.tagHtml('meta', ['name': 'description', 'content': '']) }}
	{{ tag.tagHtml('meta', ['name': 'keywords', 'content': '']) }}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width">

	<link rel="image_src" href="//<?=$_SERVER['HTTP_HOST'] ?><?=$this->url->getBaseUri() ?>assets/images/logo.jpg" />
	<link rel="apple-touch-icon" href="//<?=$_SERVER['HTTP_HOST'] ?><?=$this->url->getBaseUri() ?>assets/images/apple-touch-icon.png"/>

	<meta property="og:title" content="Вход в игру"/>
	<meta property="og:site_name" content="Звездная Империя 5"/>
	<meta property="og:description" content="Вы являетесь межгалактическим императором, который распространяет своё влияние посредством различных стратегий на множество галактик."/>

	{{ assets.outputCss() }}
	{{ assets.outputJs() }}

	<!--[if lte IE 9]>
		<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
		<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->
</head>
<body>
	<script type="text/javascript">
		var ajax_nav = 0;
		var addToUrl = '';
	</script>
	<? if ($this->dispatcher->getControllerName() !== 'index'): ?>
		<div id="box">
			<div class="game_content">
				<div class="content">
					{{ content() }}
				</div>
			</div>
		</div>
	<? else: ?>
		{{ content() }}
	<? endif; ?>
</body>
</html>