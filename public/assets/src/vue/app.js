var App = require('./app.vue')

Vue.prototype.Format = Format
Vue.prototype.date = date
Vue.prototype.morph = morph
Vue.prototype.load = load
Vue.prototype.showWindow = showWindow

var BuildingBuildController = require('./controllers/buildings/build.vue')
var GalaxyController = require('./controllers/galaxy/galaxy.vue')
var EmptyController = require('./controllers/empty.vue')

const routes = [{
	path: '/buildings',
	component: BuildingBuildController
}, {
	path: '/buildings/index/*',
	component: BuildingBuildController
}, {
	path: '/galaxy*',
	component: GalaxyController
}, {
	path: '*',
	component: EmptyController
}]

var router = new VueRouter({
	mode: 'history',
  routes // сокращение от `routes: routes`
})

window.application = new Vue({
	router,
	el: '#application',
	delimiters: ['<%', '%>'],
	data: options,
	computed: {
		getMenuActiveLink: function ()
		{
			return this.route.controller+(this.route.controller === 'buildings' ? this.route.action : '');
		}
	},
	watch: {
		html: function (val)
		{
			setTimeout(function()
			{
				this.evalJs(val);
			}.bind(this), 25)
		}
	},
	methods:
	{
		getUrl: function (url)
		{
			return this.path+url;
		},
		getPlanetUrl: function (galaxy, system, planet)
		{
			return '<a href="'+this.getUrl('galaxy/'+galaxy+'/system/'+planet+'/')+'">['+galaxy+':'+system+':'+planet+']</a>';
		},
		evalJs: function (html)
		{
			if (html.length > 0)
			{
				var j = $('<div/>').append(html)

				j.find("script").each(function()
				{
					if ($(this).attr('src') !== undefined)
						$.getScript($(this).attr('src'))
					else
						jQuery.globalEval($(this).text());
				});
			}
		}
	},
	mounted: function ()
	{
		this.evalJs(this.html);
	},
	render: h => h(App)
})