import Vue from 'vue'

import 'core-js/fn/object/assign';
import 'core-js/fn/array/find';
import 'core-js/modules/es6.promise';
import 'core-js/modules/es6.array.iterator';

require("./../js/game.js");

import router from './router'

const application = new Vue({
	router,
	el: '#application',
	data: {
		request_block: false,
		request_block_timer: null,
	},
})