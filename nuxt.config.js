module.exports = {
	head: {
		title: 'XNova Game',
		meta: [
			{ charset: 'utf-8' },
			{ name: 'viewport', content: 'width=device-width, initial-scale=1' },
			{ 'http-equiv': 'X-UA-Compatible', content: 'IE=edge' },
			{ property: 'og:title', content: 'XNova Game', hid: 'og:title' },
			{ property: 'og:image', content: '/images/logo.jpg' },
			{ property: 'og:image:width', content: '300' },
			{ property: 'og:image:height', content: '300' },
			{ property: 'og:site_name', content: 'Звездная Империя' },
			{ property: 'og:description', content: 'Вы являетесь межгалактическим императором, который распространяет своё влияние посредством различных стратегий на множество галактик.' },
		],
		link: [
			{ rel: 'image_src', href: '/images/logo.jpg' },
			{ rel: 'apple-touch-icon', href: '/images/apple-touch-icon.png' },
		]
	},
	css: [
		'~/assets/styles/bootstrap/bootstrap.scss',
		'~/assets/app.scss',
		'vuejs-dialog/dist/vuejs-dialog.min.css',
	],
	pageTransition: {
		css: false
	},
	plugins: [
		'~/plugins/api.js',
		'~/plugins/validate.js',
		'~/plugins/i18n.js',
		'~/plugins/filters.js',
		'~/plugins/components.js',
		{src: '~/plugins/global.js', ssr: false},
		{src: '~/plugins/modal.js', ssr: false},
		{src: '~/plugins/router.js', ssr: false},
		{src: '~/plugins/metrika.js', ssr: false},
		{src: '~/plugins/tooltip.js', ssr: true},
		{src: '~/plugins/swipe.js', ssr: false},
		{src: '~/plugins/toast.js', ssr: false},
		{src: '~/plugins/dialog.js', ssr: false},
	],
	router: {
		base: '/',
		middleware: 'router',
		prefetchLinks: false,
		linkExactActiveClass: 'active',
		scrollBehavior: function (to, from)
		{
			if (to.path === from.path)
				return false;
			else if (to.matched.length < 2 && from.matched.length < 2)
				return { x: 0, y: 0 };
			else if (to.matched.length >= 2 && from.matched.length < 2)
				return { x: 0, y: 0 };
			else if (to.matched.length < 2 && from.matched.length >= 2)
				return { x: 0, y: 0 };
			else
				return false;
		},
	},
	loading: {
		color: '#9a1915',
	},
	modern: process.env.NODE_ENV === 'production',
	build: {
		indicator: false,
		cssSourceMap: false,
		extractCSS: process.env.NODE_ENV === 'production',
		publicPath: '/static/',
		loaders: {
			vue: {
				compilerOptions: {
					preserveWhitespace: true
				}
			}
		},
		optimizeCSS: {
			cssProcessorPluginOptions: {
				preset: ['default', {
					discardComments: {
					removeAll: true
				}}],
			},
		},
		extend (config, {isDev, isClient})
		{
			if (isDev && isClient)
			{
				config.module.rules.push({
					enforce: 'pre',
					test: /\.(js|vue)$/,
					exclude: /(node_modules)/
				});
			}

			config.resolve.alias['socket.io-client'] = 'socket.io-client/dist/socket.io.slim.js'

			if (isClient)
			{
				config.externals = {
					"window": "window"
				};

				config.module.rules.push({
					test: /vue-router(.*?)\.js$/,
					loader: 'string-replace-loader',
					options: {
						search: 'isSameRoute(route, current)',
						replace: 'false',
					}
				})
			}
		}
	},
	modules: [
		'@nuxtjs/proxy',
		'@nuxtjs/axios',
	],
	axios: {
		prefix: '/api',
		proxy: true,
		proxyHeaders: true,
		credentials: true,
	},
	proxy: {
		'/api': {target: 'http://test.xnova.su', cookieDomainRewrite: {"*": ""}},
		'/upload': {target: 'http://test.xnova.su/upload'},
	},
	vue: {
		config: {
			productionTip: false
		}
	},
};