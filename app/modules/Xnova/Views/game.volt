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

	<meta property="og:image" content="//{{ request.getServer('HTTP_HOST') }}{{ static_url('assets/images/logo.jpg') }}"/>
	<meta property="og:image:width" content="300"/>
	<meta property="og:image:height" content="300"/>

	{{ assets.outputCss() }}
	{{ assets.outputJs() }}

	<!--[if lte IE 9]>
		<link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
		<script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
	<![endif]-->

	{% if allowMobile() is not true %}
		<meta name="viewport" content="width=810">
	{% else %}
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
	{% endif %}
</head>
<body class="{{ config.view.get('socialIframeView', 0) == 1 ? 'iframe' : 'window' }}">
	<script type="text/javascript">
		ajax_nav = 1;

		var options = {{ toJson(options) }};
	</script>

	<div id="application" v-bind:class="['set_'+route.controller]">

		<!-- header -->
		<a v-if="options.view.header" href="#" class="menu-toggle hidden-sm-up">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div v-if="options.view.header" class="menu-sidebar hidden-sm-up">
			<sidebar-menu v-bind:items="$root.menu" v-bind:active="$root.getMenuActiveLink"></sidebar-menu>
		</div>

		<application-header v-if="options.view.header"></application-header>
		<application-header-mobile-icons v-if="options.view.header"></application-header-mobile-icons>
		<!-- end header -->

		<div class="game_content">
			<!-- menu -->
			<main-menu v-if="options.view.menu" v-bind:items="menu" v-bind:active="getMenuActiveLink"></main-menu>
			<!-- end menu -->

			<!-- planets -->
			<a v-if="options.view.planets" href="#" class="planet-toggle hidden-sm-up"><span>
					<span class="first"></span>
					<span class="second"></span>
					<span class="third"></span>
				</span>
			</a>

			<application-planets-list v-if="options.view.planets" v-bind:items="user.planets"></application-planets-list>
			<!-- end planets -->

			<div class="content">
				<!-- planet panel -->
				<planet-panel v-if="options.view.resources" v-bind:planet="resources"></planet-panel>
				<!-- end planet panel -->

				<!-- messages -->
				<div v-if="options.messages" v-for="item in options.messages">
					<application-messages-row v-bind:item="item"></application-messages-row>
				</div>
				<!-- end messages -->

				<div id="gamediv" class="content-row" v-html="html"></div>
			</div>
		</div>

		<!-- footer -->
		<application-footer v-if="options.view.header"></application-footer>
		<!-- end footer -->
	</div>

	{{ partial('shared/socials') }}

	<div id="windowDialog"></div>
	<div id="tooltip" class="tip"></div>
</body>
</html>