Vue.component('main-menu', {
	props: ['items', 'active'],
	template: '<ul class="menu hidden-xs-down">' +
		'<li is="main-menu-item" v-for="item in items" v-bind:item="item"></li>' +
	'</ul>'
})

Vue.component('sidebar-menu', {
	props: ['items', 'active'],
	template: '<ul class="nav">' +
		'<li is="main-menu-item" v-for="item in items" v-bind:item="item"></li>' +
	'</ul>'
})

Vue.component('main-menu-item', {
	props: ['item'],
	render: function (createElement)
	{
		return createElement('li', {}, [
			createElement('a', {
				class: {
					active: this.$parent.active === this.item.id
				},
				attrs: {
					href: this.item.url,
					target: this.item.new === true ? '_blank' : ''
				}
			}, this.item.text)
		])
	}
})

Vue.component('planet-panel', {
	props: ['planet'],
	template: '<div class="row topnav">' +
		'<div class="col-md-6 col-sm-6 col-xs-12">' +
			'<div class="row">' +
				'<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="\'metal\'" v-bind:resource="planet.metal"></planet-panel-resource></div>' +
				'<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="\'crystal\'" v-bind:resource="planet.crystal"></planet-panel-resource></div>' +
				'<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="\'deuterium\'" v-bind:resource="planet.deuterium"></planet-panel-resource></div>' +
			'</div>' +
		'</div>' +
		'<div class="col-md-6 col-sm-6 col-xs-12">' +
			'<div class="row">' +
				'<div class="col-xs-4 text-xs-center">' +
					'<span onclick="showWindow(\'\', \'/info/4/\', 600)" title="Солнечная батарея" class="hidden-xs-down"><span class="sprite skin_energie"></span><br></span>' +
					'<div class="neutral">Энергия</div>' +
					'<div title="Энергетический баланс">' +
						'<span v-if="planet.energy.current >= 0" class="positive">{{ Format.number(planet.energy.current) }}</span>' +
						'<span v-else class="negative">{{ Format.number(planet.energy.current) }}</span>' +
					'</div>' +
					'<span title="Выработка энергии" class="hidden-xs-down positive">{{ Format.number(planet.energy.max) }}</span>' +
				'</div>' +
				'<div class="col-xs-4 text-xs-center">' +
					'<span class="tooltip hidden-xs-down">' +
						'<div class="tooltip-content"><center>Вместимость:<br>{{ Format.number(planet.battery.current) }} / {{ Format.number(planet.battery.max) }} <br> {{ planet.battery.tooltip }}</center></div>' +
						'<img v-if="planet.battery.power > 0 && planet.battery.power < 100" v-bind:src="\'/assets/images/batt.php?p=\'+planet.battery.power" width="42" alt="">' +
						'<span v-else v-bind:class="\'sprite skin_batt\'+planet.battery.power"></span>' +
						'<br>' +
					'</span>' +
					'<div class="neutral">Аккумулятор</div>' +
					'{{ planet.battery.power }}%<br>' +
				'</div>' +
				'<div class="col-xs-4 text-xs-center">' +
					'<a v-bind:href="$root.getUrl(\'credits/\')" class="tooltip hidden-xs-down">' +
						'<div class="tooltip-content">' +
							'<table width=550>' +
								'<tr>' +
									'<td v-for="(time, index) in planet.officiers" align="center" width="14%">'+
										'<div class="separator"></div>' +
										'<span v-bind:class="[\'officier\', \'of\'+index+(time > ((new Date).getTime() / 1000) ? \'_ikon\' : \'\')]"></span>' +
									'</td>' +
								'</tr>' +
								'<tr>' +
									'<td v-for="(time, index) in planet.officiers" align="center">'+
										'<span v-if="time > ((new Date).getTime() / 1000)">Нанят до <font color=lime>{{ date(\'d.m.Y H:i\', time) }}</font></span>' +
										'<span v-else><font color=lime>Не нанят</font></span>' +
									'</td>' +
								'</tr>' +
						'</div>' +
						'<span class="sprite skin_kredits"></span><br>' +
					'</a>' +
					'<div class="neutral">Кредиты</div>' +
					'{{ Format.number(planet.credits) }}<br>' +
				'</div>' +
			'</div>' +
		'</div>' +
	'</div>',
	methods:
	{
		update: function ()
		{
			if (typeof options.planet === 'undefined' || options.planet === false)
				return;

			if (XNova.lastUpdate === 0)
				XNova.lastUpdate = (new Date).getTime();

			var factor = ((new Date).getTime() - XNova.lastUpdate) / 1000;

			if (factor < 0)
				return;

			XNova.lastUpdate = (new Date).getTime();

			['metal', 'crystal', 'deuterium'].forEach(function(res)
			{
				if (typeof options.planet[res] === 'undefined')
					return;

				var power = (options.planet[res]['current'] >= options.planet[res]['max']) ? 0 : 1;

				options.planet[res]['current'] += ((options.planet[res]['production'] / 3600) * power * factor);
			});
		}
	},
	created: function ()
	{
		this.update();

		clearInterval(timeouts['res_count']);
		timeouts['res_count'] = setInterval(this.update, 1000);
	},
	updated: function ()
	{
		this.update();

		clearInterval(timeouts['res_count']);
		timeouts['res_count'] = setInterval(this.update, 1000);
	},
	destroyed: function ()
	{
		clearInterval(timeouts['res_count']);
	}
})

Vue.component('planet-panel-resource-tooltip', {
	props: ['resource'],
	template: '<table width=150>' +
		'<tr><td width=30%>КПД:</td><td align=right>{{ resource.power }}%</td></tr>' +
		'<tr><td>В час:</td><td align=right>{{ Format.number(resource.production) }}</td></tr>' +
		'<tr><td>День:</td><td align=right>{{ Format.number(resource.production * 24) }}</td></tr>' +
	'</table>'
})

Vue.component('planet-panel-resource', {
	props: ['resource', 'type'],
	template: '<div class="planet-resource-panel-item">' +
		'<span v-on:click="showPopup" class="tooltip hidden-xs-down">' +
			'<div class="tooltip-content">' +
				'<planet-panel-resource-tooltip v-bind:resource="resource"></planet-panel-resource-tooltip>' +
			'</div>' +
			'<span v-bind:class="[\'sprite\', \'skin_\'+type]"></span>' +
			'<br>' +
		'</span>' +
		'<div class="neutral">{{ resource.title }}</div>' +
		'<div title="Количество ресурса на планете">' +
			'<span v-if="resource.max > resource.current" class="positive">{{ Format.number(resource.current) }}</span>' +
			'<span v-else class="negative">{{ Format.number(resource.current) }}</span>' +
		'</div>' +
		'<span title="Максимальная вместимость хранилищ" class="hidden-xs-down">' +
			'<span v-if="resource.max > resource.current" class="positive">{{ Format.number(resource.max) }}</span>' +
			'<span v-else class="negative">{{ Format.number(resource.max) }}</span>' +
		'</span>' +
	'</div>',
	methods:
	{
		showPopup: function ()
		{
			showWindow('', this.$root.getUrl(this.resource.url), 600)
		}
	}
})

Vue.component('application-footer', {
	template: '<footer>' +
		'<div class="hidden-xs-down">' +
			'<div class="container-fluid">' +
				'<div class="pull-xs-left text-xs-left">' +
					'<a v-bind:href="$root.getUrl(\'news/\')" title="Последние изменения">{{ options.version }}</a>' +
					'<a class="hidden-sm-down" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>' +
				'</div>' +
				'<div class="pull-xs-right text-xs-right">' +
					'<a href="http://forum.xnova.su/" target="_blank">Форум</a>|' +
					'<a v-bind:href="$root.getUrl(\'banned/\')">Тёмные</a>|' +
					'<a href="//vk.com/xnova_game" target="_blank">ВК</a>|' +
					'<a v-bind:href="$root.getUrl(\'contact/\')">Контакты</a>|' +
					'<a v-bind:href="$root.getUrl(\'content/help/\')">Новичкам</a>|' +
					'<a v-bind:href="$root.getUrl(\'content/agb/\')">Правила</a>|' +
					'<a onclick="" title="Игроков в сети" style="color:green">{{ options.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ options.stats.users }}</a>' +
				'</div>' +
				'<div class="clearfix"></div>' +
			'</div>' +
		'</div>' +
		'<div class="row hidden-sm-up footer-mobile">' +
			'<div class="col-xs-12 text-xs-center">' +
				'<a href="http://forum.xnova.su/" target="_blank">Форум</a>|' +
				'<a v-bind:href="$root.getUrl(\'banned/\')">Тёмные</a>|' +
				'<a v-bind:href="$root.getUrl(\'contact/\')">Контакты</a>|' +
				'<a v-bind:href="$root.getUrl(\'content/help/\')">Новичкам</a>|' +
				'<a v-bind:href="$root.getUrl(\'content/agb/\')">Правила</a>' +
			'</div>' +
			'<div class="col-xs-8 text-xs-center">' +
				'<a v-bind:href="$root.getUrl(\'news/\')" title="Последние изменения">{{ options.version }}</a>' +
				'<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>' +
			'</div>' +
			'<div class="col-xs-4 text-xs-center">' +
				'<a onclick="" title="Игроков в сети" style="color:green">{{ options.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ options.stats.users }}</a>' +
			'</div>' +
		'</div>' +
	'</footer>'
})

var application;

$(document).ready(function()
{
	application = new Vue({
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
				return options.path+url;
			}
		}
	})
});