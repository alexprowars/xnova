import { toast } from 'vue3-toastify';
import { router } from '@inertiajs/vue3';
import ToastMessage from '~/components/ToastMessage.vue';

export default {
	install(_app) {
		_app.use(Vue3Toastify, {
			autoClose: 3000,
			position: toast.POSITION.TOP_CENTER,
			clearOnUrlChange: false,
			pauseOnHover: false,
			pauseOnFocusLoss: false,
			dangerouslyHTMLString: true,
			theme: 'auto',
		});

		router.on('flash', (event) => {
			const notifications = event.detail.flash.notifications || [];

			notifications.forEach(notification => {
				if (notification?.body) {
					toast(ToastMessage, {
						contentProps: {
							title: notification?.title || null,
							content: notification.body,
						},
						type: notification?.type || 'default',
					});
				}
			})
		});
	}
}