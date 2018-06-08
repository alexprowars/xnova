import Vue from 'vue'
import Vuex from 'vuex'
import VueRouter from 'vue-router'

import 'es6-promise/auto'

import App from './app.vue'

Vue.config.productionTip = false;

Vue.prototype.morph = morph
Vue.prototype.load = load
Vue.prototype.isMobile = isMobile

import Lang from './js/lang'
import Format from './js/format'

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

import BuildingBuildController from './controllers/buildings/build.vue'
import BuildingTechController from './controllers/buildings/tech.vue'
import BuildingUnitController from './controllers/buildings/unit.vue'
import GalaxyController from './controllers/galaxy/galaxy.vue'
import OverviewController from './controllers/overview/overview.vue'
import FleetIndexController from './controllers/fleet/fleet-index.vue'
import FleetOneController from './controllers/fleet/fleet-one.vue'
import FleetTwoController from './controllers/fleet/fleet-two.vue'
import ChatController from './controllers/chat/chat.vue'
import MessagesController from './controllers/messages/messages.vue'
import MerchantController from './controllers/merchant/merchant.vue'
import AllianceChatController from './controllers/alliance/alliance_chat.vue'
import PlayersStatController from './controllers/players/players_stat.vue'
import NotesController from './controllers/notes/notes.vue'
import PhalanxController from './controllers/phalanx/phalanx.vue'
import IndexController from './controllers/index/index.vue'
import HtmlController from './controllers/html.vue'

const routes = [{
	path: '/buildings/research*',
	component: BuildingTechController
}, {
	path: '/buildings/fleet*',
	component: BuildingUnitController
}, {
	path: '/buildings/defense*',
	component: BuildingUnitController
}, {
	path: '/buildings*',
	component: BuildingBuildController
}, {
	path: '/galaxy*',
	component: GalaxyController
}, {
	path: '/phalanx*',
	component: PhalanxController
}, {
	path: '/fleet/one',
	component: FleetOneController
}, {
	path: '/fleet/two',
	component: FleetTwoController
}, {
	path: '/fleet*',
	component: FleetIndexController
}, {
	path: '/overview*',
	component: OverviewController
}, {
	path: '/chat',
	component: ChatController
}, {
	path: '/messages',
	component: MessagesController
}, {
	path: '/merchant',
	component: MerchantController
}, {
	path: '/notes',
	component: NotesController
}, {
	path: '/alliance/chat',
	component: AllianceChatController
}, {
	path: '/alliance/stat/id/:ally_id',
	component: PlayersStatController
}, {
	path: '/players/stat/:user_id',
	component: PlayersStatController
}, {
	path: '/',
	component: IndexController
}, {
	path: '*',
	component: HtmlController
}];

Vue.component('error-message', require('./views/message.vue'))
Vue.component('tab', require('./components/tab.vue'));
Vue.component('tabs', require('./components/tabs.vue'));
Vue.component('pagination', require('./components/pagination.vue'));
Vue.component('text-viewer', require('./components/text-viewer.vue'));
Vue.component('text-editor', require('./components/text-editor.vue'));
Vue.component('number', require('./components/number.vue'));
Vue.component('chat', require('./components/chat.vue'));

Vue.use(Vuex);
Vue.use(VueRouter);

let router = new VueRouter({
	mode: 'history',
	routes
})

router.beforeEach(function(to, from, next)
{
	if (from.name === null && typeof from.name === "object")
		return next();

	if (router.app.router_block)
	{
		router.app.router_block = false;
		return next();
	}

	router.app.loadPage(to.fullPath, function(url)
	{
		next();

		if (to.path !== url)
		{
			router.app.router_block = true;
			router.replace(url, () => router.app.router_block = false, () => router.app.router_block = false);
		}
	});
})

window.store = new Vuex.Store({
	state: options,
	mutations: {
		PAGE_LOAD (state, data)
		{
			application.start_time = Math.floor(((new Date()).getTime()) / 1000)

			for (let key in data)
			{
				if (data.hasOwnProperty(key))
					state[key] = data[key];
			}
		}
	}
});

window.application = new Vue({
	router,
	store,
	el: '#application',
	computed: {
		getMenuActiveLink: function () {
			return this.$store.state['route']['controller']+(this.$store.state['route']['controller'] === 'buildings' ? this.$store.state['route']['action'] : '');
		},
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
		router_block: false,
		start_time: Math.floor(((new Date()).getTime()) / 1000),
		html_component: null
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
		url (val) {
			this.router_block = true;
			this.$router.push(val, () => this.router_block = false, () => this.router_block = false);

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
		evalJs: function (html)
		{
			if (html.length > 0)
			{
				let j = $('<div/>').append(html)

				j.find("script").each(function()
				{
					if ($(this).attr('src') !== undefined)
						$.cachedScript($(this).attr('src'))
					else
						jQuery.globalEval($(this).text());
				});
			}
		},
		serverTime () {
			return Math.floor((new Date).getTime() / 1000) + this.$store.state.stats.time - this.start_time;
		},
		applyData: function (data) {
			this.$store.commit('PAGE_LOAD', data);
		},
		loadPage (url, callback)
		{
			if (this.request_block)
				return;

			this.request_block = true;
			this.loader = true;

			this.request_block_timer = setTimeout(() => {
				this.request_block = false
			}, 500);

			$.ajax({
				url: url,
				cache: false,
				dataType: 'json',
				timeout: 10000
			})
			.then((result) =>
			{
				closeWindow();

				this.applyData(result.data);

				if (typeof result.data['tutorial'] !== 'undefined' && result.data['tutorial']['popup'] !== '')
				{
					$.confirm({
						title: 'Обучение',
						content: result.data['tutorial']['popup'],
						confirmButton: 'Продолжить',
						cancelButton: false,
						backgroundDismiss: false,
						confirm: function ()
						{
							if (result.data['tutorial']['url'] !== '')
								load(result.data['tutorial']['url']);
						}
					});
				}

				if (typeof result.data['tutorial'] !== 'undefined' && result.data['tutorial']['toast'] !== '')
				{
					$.toast({
						text: result.data['tutorial']['toast'],
						icon: 'info',
						stack : 1
					});
				}

				callback && callback(result.data.url);
			}, () => {
				document.location = url;
			})
			.always(() =>
			{
				this.loader = false;
				this.request_block = false;

				clearTimeout(this.request_block_timer);
			})
		},
		init ()
		{
			let body = $('body');
			let app = this;

			body.on('mouseenter', '.tooltip', function()
			{
				if (isMobile)
					return;

				let _this = $(this);

				let status = false;

				try {
					status = _this.tooltipster('status');
				} catch (err) {}

				if (status)
					return;

				let maxWidth = null;

				if (_this.data('width') !== undefined)
					maxWidth = parseInt(_this.data('width'));

				_this.tooltipster({
					delay: 100,
					distance: 0,
					maxWidth: maxWidth,
					contentAsHTML: true,
					interactive: _this.hasClass('sticky'),
					functionInit: function(instance)
					{
						if (_this.hasClass('script'))
							instance.content(eval(_this.data('content')));
						else if (typeof _this.data('content') === "undefined")
							instance.content(_this.find('.tooltip-content'));
						else
							instance.content(_this.data('content'));
					}
				}).tooltipster('open');
			})
			.on('click', '.tooltip', function()
			{
				if (!isMobile)
					return;

				let _this = $(this);

				let status = false;

				try {
					status = _this.tooltipster('status');
				} catch (err) {}

				if (!_this.hasClass('sticky') && status)
				{
					if (status.open)
						_this.tooltipster('close');
					else
						_this.tooltipster('open');

					return;
				}

				if (typeof _this.data('tooltipster-ns') !== 'undefined')
				{
					_this.tooltipster('open');
					return;
				}

				let maxWidth = null;

				if (_this.data('width') !== undefined)
					maxWidth = parseInt(_this.data('width'));

				_this.tooltipster({
					delay: 100,
					distance: 0,
					maxWidth: maxWidth,
					contentAsHTML: true,
					interactive: _this.hasClass('sticky'),
					functionInit: function(instance)
					{
						if (_this.hasClass('script'))
							instance.content(eval(_this.data('content')));
						else if (typeof _this.data('content') === "undefined")
							instance.content(_this.find('.tooltip-content'));
						else
							instance.content(_this.data('content'));
					}
				}).tooltipster('open');
			})
			.on('click', 'a', function(e)
			{
				let el = $(this);
				let url = el.attr('href');

				if (!url || el.hasClass('window') || url.indexOf('#') === 0)
					return false;

				if (url.indexOf('javascript') === 0 || url.indexOf('mailto') === 0 || url.indexOf('#') >= 0 || el.attr('target') === '_blank')
					return true;
				else
				{
					e.preventDefault();

					load(url);
				}

				return false;
			})
			.on('click', '.main-content form[class!=noajax] input[type=submit], .main-content form[class!=noajax] button[type=submit]', function(e)
			{
				e.preventDefault();

				let button = $(this);
				let form = button.closest('form');

				form.append($('<input/>', {type: 'hidden', name: button.attr('name'), value: button.attr('value')}));
				form.submit();
			})
			.on('submit', '.main-content form[class!=noajax]', function(e)
			{
				e.preventDefault();

				let form = $(this);

				app.loader = true;

				let formData = new FormData(this);

				$.ajax({
				    url: form.attr('action'),
				    data: formData,
				    type: 'post',
					dataType: 'json',
				    contentType: false,
				    processData: false
				})
				.then((result) => {
					app.applyData(result.data);
				}, () => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
				.always(() => {
					app.loader = false;
				})
			})
			.on('submit', '.jconfirm-dialog form', function(e)
			{
				e.preventDefault();

				app.loader = true;

				let formData = new FormData(this);
				formData.append('popup', 'Y');

				$.ajax({
					url: $(this).attr('action'),
					type: 'post',
					data: formData,
					dataType: 'json',
					processData: false,
					contentType: false
				})
				.then((result) =>
				{
					if (result.data.messages.length > 0 )
					{
						result.data.messages.forEach(function(item)
						{
							if (item['type'].indexOf('-static') <= 0)
							{
								$.toast({
									text: item.message,
									icon: item.type
								});
							}
						})
					}

					if (result.data.html !== '')
					{
						if (app.html_component !== null)
							app.html_component.$destroy();

						app.html_component = new (Vue.extend({
							name: 'html-render',
							template: '<div>'+result.data.html.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')+'</div>'
						}))().$mount();

						Vue.nextTick(() =>
						{
							$('.jconfirm-content').html(app.html_component.$el);

							setTimeout(() => {
								app.evalJs(result.data.html);
							}, 100);
						});
					}

					app.$store.state.redirect = result.data.redirect;
				}, () => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
				.always(() => {
					app.loader = false;
				})
			})
			.on('click', '.popup-user', function(e)
			{
				e.preventDefault();

				app.openPopup('', $(this).attr('href'))
			});
		},
		openPopup (title, url, width)
		{
			if (isMobile)
				return window.location.href = url.split('ajax').join('').split('popup').join('');

			let app = this;

			if (width === undefined)
				width = 600;

			$.dialog({
				title: '',
				theme: 'dialog',
				useBootstrap: false,
				boxWidth: width,
				backgroundDismiss: true,
				animation: 'opacity',
				closeAnimation: 'opacity',
				animateFromElement: false,
				draggable: false,
				content: function ()
				{
					return $.ajax({
						url: url,
						type: 'get',
						data: {'popup': 'Y'},
						success: (result) =>
						{
							this.setTitle(result.data.title);

							if (app.html_component !== null)
								app.html_component.$destroy();

							app.html_component = new (Vue.extend({
								name: 'html-render',
								template: '<div>'+result.data.html.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')+'</div>'
							}))().$mount();

							Vue.nextTick(() => {
								this.setContent(app.html_component.$el, true);

								setTimeout(() => {
									app.evalJs(result.data.html);
								}, 100);
							});
						}
					});
				}
			});
		}
	},
	render: h => h(App)
})