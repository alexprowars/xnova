var App = require('./app.vue')

Vue.prototype.Format = Format
Vue.prototype.date = date

var application = new Vue({
	el: '#application',
	delimiters: ['<%', '%>'],
	data: options,
	computed: {
		getMenuActiveLink: function ()
		{
			return this.route.controller+(this.route.controller === 'buildings' ? this.route.action : '');
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