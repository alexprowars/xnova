let App = require('./app.vue')

Vue.prototype.Format = Format
Vue.prototype.date = date
Vue.prototype.morph = morph
Vue.prototype.load = load
Vue.prototype.showWindow = showWindow
Vue.prototype.QuickFleet = QuickFleet

let BuildingBuildController = require('./controllers/buildings/build.vue')
let GalaxyController = require('./controllers/galaxy/galaxy.vue')
let OverviewController = require('./controllers/overview/overview.vue')
let HtmlController = require('./controllers/html.vue')

const routes = [{
	path: '/buildings',
	name: 'buildings',
	component: BuildingBuildController
}, {
	path: '/buildings/index/*',
	name: 'buildings-index',
	component: BuildingBuildController
}, {
	path: '/galaxy*',
	name: 'galaxy',
	component: GalaxyController
}, {
	path: '/overview',
	name: 'overview',
	component: OverviewController
}, {
	path: '*',
	name: 'html',
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

	router.app.loadPage(to.path, function(url)
	{
		next();

		if (to.path !== url)
			router.replace(url);
	});
})

let store = new Vuex.Store({
	state: options,
	mutations: {
		load (state, data)
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
			return this.$store.state.route.controller+(this.$store.state.route.controller === 'buildings' ? this.$store.state.route.action : '');
		},
		html () {
			return this.$store.state.html;
		},
		title () {
			return this.$store.state.title_full;
		},
		url () {
			return this.$store.state.url;
		},
		redirect () {
			return this.$store.state.redirect;
		},
		messages () {
			return this.$store.state.messages;
		}
	},
	watch: {
		html: function (val)
		{
			setTimeout(function() {
				this.evalJs(val);
				TextParser.parseAll();
			}.bind(this), 25)
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
			this.$router.push(val);
		}
	},
	methods:
	{
		getUrl: function (url)
		{
			return this.$store.state.path+url;
		},
		getPlanetUrl: function (galaxy, system, planet)
		{
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
		applyData: function (data) {
			this.$store.commit('load', data);
		},
		loadPage (url, callback)
		{
			if (!blockTimer)
				return;

			clearTimers();

			blockTimer = false;

			showLoading();

			$('[role="tooltip"]').remove()

			$.ajax(
			{
				url: url,
				cache: false,
				dataType: 'json',
				success: function(result)
				{
					$('#tooltip').hide();
					hideLoading();
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
				}.bind(this),
				timeout: 10000,
				error: function(jqXHR, exception)
				{
					console.log(jqXHR.responseText);
					console.log(exception);

					$('#tooltip').hide();
					document.location = to.path;
				}
			});
		}
	},
	mounted: function ()
	{
		this.evalJs(this.$store.state.html);
	},
	render: h => h(App)
})