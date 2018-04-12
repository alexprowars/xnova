
import 'es6-promise/auto'

let App = require('./app.vue')

Vue.prototype.Format = Format
Vue.prototype.date = date
Vue.prototype.morph = morph
Vue.prototype.load = load
Vue.prototype.showWindow = showWindow
Vue.prototype.Lang = Lang
Vue.prototype.isMobile = isMobile

let parser = require('./js/parser.js');

Vue.prototype.parser = parser.parser;

let BuildingBuildController = require('./controllers/buildings/build.vue')
let BuildingTechController = require('./controllers/buildings/tech.vue')
let BuildingUnitController = require('./controllers/buildings/unit.vue')
let GalaxyController = require('./controllers/galaxy/galaxy.vue')
let OverviewController = require('./controllers/overview/overview.vue')
let FleetIndexController = require('./controllers/fleet/fleet-index.vue')
let FleetOneController = require('./controllers/fleet/fleet-one.vue')
let FleetTwoController = require('./controllers/fleet/fleet-two.vue')
let ChatController = require('./controllers/chat/chat.vue')
let MessagesController = require('./controllers/messages/messages.vue')
let MerchantController = require('./controllers/merchant/merchant.vue')
let AllianceChatController = require('./controllers/alliance/alliance_chat.vue')
let PlayersStatController = require('./controllers/players/players_stat.vue')
let NotesController = require('./controllers/notes/notes.vue')
let PhalanxController = require('./controllers/phalanx/phalanx.vue')
let IndexController = require('./controllers/index/index.vue')
let HtmlController = require('./controllers/html.vue')

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
		router_block: false,
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
		url (val) {
			this.router_block = true;
			this.$router.push(val, () => this.router_block = false, () => this.router_block = false);

			$('body').attr('page', this.$store.state.route.controller);
		},
		loader (val, old)
		{
			if (val === old)
				return;

			if (val)
				setTimeout(() => this.request_block = false, 500);
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
			.always(() => {
				this.loader = false;
			})
		}
	},
	render: h => h(App)
})