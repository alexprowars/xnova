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
		serverTime () {
			return Math.floor((new Date).getTime() / 1000) + this.$store.state.stats.time - this.start_time;
		}
	},
	render: h => h(App)
})

export default application