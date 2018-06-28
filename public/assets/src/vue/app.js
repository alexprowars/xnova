import './../bootstrap/bootstrap.scss';
import './../game/style.scss';

import 'core-js/fn/object/assign';
import 'core-js/fn/promise';

import Vue from 'vue'
import Vuelidate from 'vuelidate'

import { $get } from 'api'

Vue.config.productionTip = false;

Vue.use(Vuelidate)

import App from './app.vue'
import Lang from './js/lang'
import Format from './js/format'

Vue.filter("morph", (value, titles) => {
	return Format.morph(value, titles);
});

Vue.filter("upper", (value) => {
	return value.toUpperCase();
});

Vue.filter("lower", (value) => {
	return value.toLowerCase();
});

Vue.filter("number", (value) => {
	return Format.number(value);
});

Vue.filter("date", (value, format) => {
	return Format.date(format, value);
});

Vue.filter("time", (value, separator, full) => {
	return Format.time(value, separator, full);
});

import store from './store'
import router from './router'

import './components'

const application = new Vue({
	router,
	store,
	el: '#application',
	computed: {
		title () {
			return this.$store.state['title'];
		},
		url () {
			return this.$store.state['url'];
		},
		redirect () {
			return this.$store.state['redirect'];
		},
		messages () {
			return this.$store.state['messages'];
		}
	},
	data: {
		loader: false,
		request_block: false,
		request_block_timer: null,
		start_time: Math.floor(((new Date()).getTime()) / 1000)
	},
	watch: {
		title (val) {
			document.title = val;
		},
		redirect (val)
		{
			if (val.length > 0)
				window.location.href = val;
		},
		messages (val)
		{
			val.forEach((item) =>
			{
				if (item['type'].indexOf('-static') <= 0)
				{
					$.toast({
						text: item.text,
						icon: item.type
					});
				}
			})
		},
		url () {
			$('body').attr('page', this.$store.state.route.controller);
		}
	},
	methods:
	{
		getUrl: function (url) {
			return this.$store.state.path + url.replace(/^\//g, '');
		},
		getLang ()
		{
			let lang = 'ru';
			let value = false;

			if (typeof Lang[lang][arguments[0]] !== 'undefined')
				value = Lang[lang][arguments[0]];

			if (arguments.length > 1)
			{
				for (let i = 0; i < arguments.length; i++)
				{
					if (i > 0 && value instanceof Object)
					{
						if (typeof value[arguments[i]] !== 'undefined')
							value = value[arguments[i]];
						else
							value = false;
					}
				}
			}

			if (value !== false)
				return value;
			else
				return '##'+$.makeArray(arguments).join('::').toUpperCase()+'##';
		},
		getPlanetUrl: function (galaxy, system, planet) {
			return '<a href="'+this.getUrl('galaxy/'+galaxy+'/'+system+'/'+planet+'/')+'">['+galaxy+':'+system+':'+planet+']</a>';
		},
		serverTime () {
			return Math.floor((new Date).getTime() / 1000) + this.$store.state.stats.time - this.start_time;
		},
		load (url)
		{
			this.loader = true;
			this.$router.push(url);
		},
		loadPage (url)
		{
			return new Promise((resolve, reject) =>
			{
				if (this.request_block)
					return reject('request block');

				this.request_block = true;
				this.loader = true;

				this.request_block_timer = setTimeout(() => {
					this.request_block = false
				}, 500);

				$get(url).then((data) =>
				{
					if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['popup'] !== '')
					{
						$.confirm({
							title: 'Обучение',
							content: data['tutorial']['popup'],
							confirmButton: 'Продолжить',
							cancelButton: false,
							backgroundDismiss: false,
							confirm: () =>
							{
								if (data['tutorial']['url'] !== '')
									this.load(data['tutorial']['url']);
							}
						});
					}

					if (typeof data['tutorial'] !== 'undefined' && data['tutorial']['toast'] !== '')
					{
						$.toast({
							text: data['tutorial']['toast'],
							icon: 'info',
							stack : 1
						});
					}

					resolve(data)
				}, () => {
					reject();
					document.location = url;
				})
				.then(() =>
				{
					this.loader = false;
					this.request_block = false;

					clearTimeout(this.request_block_timer);
				})
			});
		},
		openPopup (title, url, width)
		{
			if (this.$store.state.mobile)
				return window.location.href = url.split('ajax').join('').split('popup').join('');

			let app = this;

			if (width === undefined)
				width = 600;

			$.dialog({
				title: title,
				theme: 'dialog',
				useBootstrap: false,
				boxWidth: width,
				backgroundDismiss: true,
				animation: 'opacity',
				closeAnimation: 'opacity',
				animateFromElement: false,
				draggable: false,
				content ()
				{
					let promise = new $.Deferred();

					$get(url, {
						'popup': 'Y'
					})
					.then(result => {
						promise.resolve(result);
					})
					.catch((error) => {
						promise.reject(error)
					})

					promise.then((result) =>
					{
						if (title === '')
							this.setTitle(result.title);

						let component = app.$router.getMatchedComponents(url)

						if (component.length)
						{
							if (typeof component[0] === 'object')
							{
								let com = new (Vue.extend(Object.assign(component[0], {parent: app})))().$mount()

								if (com && com.$data.page !== undefined)
								{
									com.setPageData(result.page)

									if (typeof com.afterLoad === 'function')
									{
										Vue.nextTick(() => {
											com.afterLoad()
										})
									}
								}

								this.setContent(com.$el, true);
							}
							else
							{
								component[0]().then((r) =>
								{
									let com = new (Vue.extend(Object.assign(r.default, {parent: app})))().$mount()

									if (com && com.$data.page !== undefined)
									{
										com.setPageData(result.page)

										if (typeof com.afterLoad === 'function')
										{
											Vue.nextTick(() => {
												com.afterLoad()
											})
										}
									}

									this.setContent(com.$el, true);
								});
							}
						}
					});

					return promise.promise();
				}
			});
		}
	},
	render: h => h(App)
})

export default application