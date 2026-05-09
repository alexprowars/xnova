import { useApiPost } from '~/composables/useApi.js';
import { router } from '@inertiajs/vue3';
import { useErrorNotification } from '~/composables/useToast.js';

export function addScript (url)
{
	let script = document.createElement('script');
	script.setAttribute('src', url);

	document.head.appendChild(script);
}

export function isSSR () {
	return typeof window === 'undefined';
}

export async function changePlanet(id) {
	try {
		await useApiPost('/user/planet', { id });

		router.reload();
	} catch (e) {
		useErrorNotification(e.message);
	}
}

export const isMobile = () => {
	return typeof window !== 'undefined' ? /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.userAgent) : false
};