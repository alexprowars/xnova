let App = require('./app.vue')

Vue.prototype.Format = Format
Vue.prototype.date = date
Vue.prototype.morph = morph
Vue.prototype.load = load
Vue.prototype.showWindow = showWindow
Vue.prototype.QuickFleet = QuickFleet

let BuildingBuildController = require('./controllers/buildings/build.vue')
let BuildingTechController = require('./controllers/buildings/tech.vue')
let BuildingUnitController = require('./controllers/buildings/unit.vue')
let GalaxyController = require('./controllers/galaxy/galaxy.vue')
let OverviewController = require('./controllers/overview/overview.vue')
let HtmlController = require('./controllers/html.vue')

const routes = [{
	path: '/buildings',
	alias: '/buildings/index/*',
	component: BuildingBuildController
}, {
	path: '/buildings/research',
	alias: '/buildings/research/*',
	component: BuildingTechController
}, {
	path: '/buildings/fleet',
	alias: '/buildings/fleet/*',
	component: BuildingUnitController
}, {
	path: '/buildings/defense',
	alias: '/buildings/defense/*',
	component: BuildingUnitController
}, {
	path: '/galaxy*',
	component: GalaxyController
}, {
	path: '/overview',
	component: OverviewController
}, {
	path: '*',
	component: HtmlController
}]

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

	router.app.loadPage(to.path, function(url)
	{
		next();

		if (to.path !== url)
		{
			router.app.router_block = true;
			router.replace(url, () => router.app.router_block = false, () => router.app.router_block = false);
		}
	});
})

let store = new Vuex.Store({
	state: options,
	mutations: {
		PAGE_LOAD (state, data)
		{
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
		html () {
			return this.$store.state['html'];
		},
		title () {
			return this.$store.state['title_full'];
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
		router_block: false
	},
	watch: {
		html: function (val)
		{
			setTimeout(() => {
				this.evalJs(val);
				TextParser.parseAll();
			}, 25)
		},
		title (val) {
			document.title = val;
		},
		redirect (val) {
			window.location.href = val;
		},
		messages (val)
		{
			val.forEach(function(item)
			{
				$.toast({
					text: item.text,
					icon: item.type
				});
			})
		},
		url (val) {
			this.router_block = true;
			this.$router.push(val, () => this.router_block = false, () => this.router_block = false);
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
			return this.$store.state.path+url;
		},
		getPlanetUrl: function (galaxy, system, planet) {
			return '<a href="'+this.getUrl('galaxy/'+galaxy+'/system/'+planet+'/')+'">['+galaxy+':'+system+':'+planet+']</a>';
		},
		evalJs: function (html)
		{
			if (html.length > 0)
			{
				let j = $('<div/>').append(html)

				j.find("script").each(function()
				{
					if ($(this).attr('src') !== undefined)
						$.getScript($(this).attr('src'))
					else
						jQuery.globalEval($(this).text());
				});
			}
		},
		serverTime () {
			return Math.floor(((new Date).getTime() / 1000));
		},
		applyData: function (data) {
			this.$store.commit('PAGE_LOAD', data);
		},
		loadPage (url, callback)
		{
			if (this.request_block)
				return;

			clearTimers();

			this.request_block = true;
			this.loader = true;

			$('[role="tooltip"]').remove()

			$.ajax(
			{
				url: url,
				cache: false,
				dataType: 'json',
				timeout: 10000,
				success: (result) =>
				{
					clearTimers();

					$('.ui-helper-hidden-accessible').html('');

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
				},
				error: () => {
					document.location = to.path;
				},
				complete: () => {
					this.loader = false;
					$('#tooltip').hide();
				}
			});
		}
	},
	mounted: function () {
		this.evalJs(this.$store.state.html);
	},
	render: h => h(App)
})