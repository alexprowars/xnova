{{ getDoctype() }}
<html lang="ru">
<head>
	{{ getTitle() }}
	{{ tag.tagHtml('meta', ['name': 'description', 'content': '']) }}
	{{ tag.tagHtml('meta', ['name': 'keywords', 'content': '']) }}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="image_src" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}" />
	<link rel="apple-touch-icon" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/apple-touch-icon.png') }}"/>

	{{ assets.outputCss() }}
	{{ assets.outputJs() }}
</head>
<body class="{{ config.view.get('socialIframeView', 0) == 1 ? 'iframe' : 'window' }}">
	<div id="application" class="set_error">
		<main>
			<div class="main-content">
				<div class="main-content-row">
					{{ content() }}
				</div>
			</div>
		</main>
	</div>

	{{ partial('shared/counters') }}

	{{ assets.outputCss('footer') }}
	{{ assets.outputJs('footer') }}
</body>
</html>