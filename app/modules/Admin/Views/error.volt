{{ getDoctype() }}
<html lang="ru">
	<head>
		<meta charset="utf-8"/>
		{{ getTitle() }}
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta content="width=device-width, initial-scale=1" name="viewport"/>
		<meta content="" name="description"/>
		<meta content="Olympia Digital" name="author"/>

		{{ assets.outputCss('css') }}
		{{ assets.outputJs('js') }}

		<link rel="shortcut icon" href="/favicon.ico"/>
	</head>
	<body class=" page-404-3">
		<div class="page-inner">
			<img src="/assets/images/earth.jpg" class="img-responsive" alt="">
		</div>
		<div class="container error-404">
			<h1>404</h1>
			<h2>Хьюстон, у нас проблема.</h2>
			<p>На самом деле, страница, которую вы ищете, не существует.</p>
			<p>
				<a href="/" class="btn red btn-outline">На главную</a><br>
			</p>
		</div>
	</body>
</html>