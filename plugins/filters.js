import Vue from 'vue'
import { number, morph, date, time } from '~/utils/format'

export default ({ store }) =>
{
	Vue.filter("morph", (value, ...titles) => {
		return morph(value, titles);
	});

	Vue.filter("upper", (value) => {
		return value.toUpperCase();
	});

	Vue.filter("lower", (value) => {
		return value.toLowerCase();
	});

	Vue.filter("number", (value) => {
		return number(value);
	});

	Vue.filter("date", (value, format) =>
	{
		value += (new Date()).getTimezoneOffset() * 60;

		if (store.state['stats'] && store.state['stats']['timezone'])
			value += store.state['stats']['timezone'];

		if (store.state['user'] && store.state['user']['timezone'])
			value += store.state['user']['timezone'] * 1800;

		return date(format, value);
	});

	Vue.filter("time", (value, separator, full) => {
		return time(value, separator, full);
	});
}