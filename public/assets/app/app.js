import Vue from 'vue'

import 'core-js/fn/object/assign';
import 'core-js/fn/array/find';
import 'core-js/modules/es6.promise';
import 'core-js/modules/es6.array.iterator';

require("./../js/game.js");

import router from './router'

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
})

window.application = application

export default application