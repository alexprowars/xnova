{{ getDoctype() }}
<html lang="ru">
<head>
	{{ getTitle() }}
	{{ tag.tagHtml('meta', ['name': 'description', 'content': '']) }}
	{{ tag.tagHtml('meta', ['name': 'keywords', 'content': '']) }}
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<link rel="image_src" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}">
	<link rel="apple-touch-icon" href="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/apple-touch-icon.png') }}">

	<meta property="og:title" content="Вход в игру">
	<meta property="og:image" content="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}">
	<meta property="og:image:width" content="300">
	<meta property="og:image:height" content="300">
	<meta property="og:site_name" content="Звездная Империя 5">
	<meta property="og:description" content="Вы являетесь межгалактическим императором, который распространяет своё влияние посредством различных стратегий на множество галактик.">

	{{ assets.outputCss() }}
	{{ assets.outputJs() }}

	<!--[if lte IE 9]>
		<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all">
		<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->

	{% if allowMobile() is not true %}
		<meta name="viewport" content="width=810">
	{% else %}
		<meta name="viewport" content="width=device-width, initial-scale=1">
	{% endif %}
</head>
<body class="{{ config.view.get('socialIframeView', 0) == 1 ? 'iframe' : 'window' }}">
	<script type="text/javascript">
		var options = {{ toJson(options) }};

		if (!options['page'])
		{
			options['page'] = {};
			options['page']['html'] = {{ replace("\t\t", "", toJson(content())) }};
		}
	</script>

	<div id="application"></div>

	{{ partial('shared/counters') }}

	{{ assets.outputJs('footer_js') }}
	{{ assets.outputCss('footer_css') }}

	{{ partial('shared/svg') }}
</body>
</html>