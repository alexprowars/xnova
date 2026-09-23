import { useForm } from '@inertiajs/vue3';

export function isSSR () {
	return typeof window === 'undefined';
}

export function changePlanet(id, options = {}) {
	useForm({ id }).post('/user/planet', {
		preserveScroll: true,
		preserveUrl: true,
		...options,
	});
}

export const isMobile = () => {
	return typeof window !== 'undefined' ? /Android|Mini|webOS|iPhone|iPad|iPod|BlackBerry/i.test(window.navigator.userAgent) : false
};