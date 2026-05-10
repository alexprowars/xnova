import { createInertiaApp } from '@inertiajs/vue3';
import i18n from './i18n.js';
import './styles.css';
import toastPlugin from './plugins/toast';
import { morph, number, time } from './utils/format.js';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import dayOfYear from 'dayjs/plugin/dayOfYear';
import weekOfYear from 'dayjs/plugin/weekOfYear';
import customParseFormat from 'dayjs/plugin/customParseFormat';
import relativeTime from 'dayjs/plugin/relativeTime';
import en from 'dayjs/locale/en';
import ru from 'dayjs/locale/ru';
import App from './App.vue';
import DefaultLayout from './layouts/DefaultLayout.vue';
import FloatingVue from 'floating-vue';
import Vue3TouchEvents from 'vue3-touch-events'
import { createVfm } from 'vue-final-modal';
import { putConfig, withInertiaModal } from '@inertiaui/modal-vue';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(dayOfYear);
dayjs.extend(weekOfYear);
dayjs.extend(customParseFormat);
dayjs.extend(relativeTime);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
	title: (title) => (title ? `${title} - ${appName}` : appName),
	layout: () => {
		return [App, DefaultLayout];
	},
	defaults: {
		visitOptions: (href, options) => {
			return {
				headers: {
					...options.headers,
					'Locale': i18n.global.locale.value,
				},
			};
		},
	},
	withApp(app) {
		withInertiaModal(app);
		app.use(i18n);

		app.config.globalProperties.$morph = (value, ...titles) => {
			return morph(value, titles);
		};

		app.config.globalProperties.$formatDate = (value, format) => {
			return dayjs(value).tz().format(format)
		};

		app.config.globalProperties.$formatNumber = number;
		app.config.globalProperties.$formatTime = time;

		dayjs.locale(en, null, true);
		dayjs.locale(ru, null, true);

		app.use(FloatingVue);
		app.use(Vue3TouchEvents, {
			touchHoldTolerance: 100,
		});

		app.use(createVfm());

		app.use(toastPlugin);

		app.config.errorHandler = (error) => {
			console.error(error);
		}
	},
	progress: {
		color: '#20529a',
	},
});

putConfig({
	useNativeDialog: false,
});
