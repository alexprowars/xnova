import { useForm, useHttp } from '@inertiajs/vue3';
import i18n from '~/i18n.js';
import { startLoading, stopLoading } from '~/composables/useLoading.js';
import { useErrorNotification } from '~/composables/useToast.js';

export const useApiGet = async (url, params = {}) => {
	if (!url.startsWith('/')) {
		url = '/' + url;
	}

	try {
		return await useHttp(params)
			.get(url, {
				headers: {
					'Locale': i18n.global.locale.value,
				},
			});
	} catch (e) {
		return handleError(e);
	}
}

export async function useApiPost (url, data = {}, options = {}){
	if (!url.startsWith('/')) {
		url = '/' + url;
	}

	try {
		return await useHttp(data)
			.post(url, {
				...options,
				headers: {
					'Locale': i18n.global.locale.value,
				},
			});
	} catch (e) {
		return handleError(e);
	}
}

export async function useApiForm (url, data = {}, options = {}){
	if (!url.startsWith('/')) {
		url = '/' + url;
	}

	try {
		return await new Promise((resolve, reject) => {
			useForm(data).post(url, {
				...options,
				headers: {
					'Locale': i18n.global.locale.value,
				},
				onSuccess: (res) => {
					resolve(res);
				},
				onError: (res) => {
					console.log(res);
					reject(res);
				},
				onNetworkError: (res) => {
					console.log(res);
				},
				onHttpException: (res) => {
					console.log(res);
				}
			});
		})

	} catch (e) {
		return handleError(e);
	}
}

export const useApiSubmit = async (url, data = {}, callback, error) => {
	startLoading();

	try {
		const result = await useApiPost(url, data, {
			forceFormData: true,
		});

		if (typeof callback === 'function') {
			callback?.(result);
		}
	} catch (e) {
		if (e.message) {
			useErrorNotification(e.message);
		}

		error?.();
	} finally {
		stopLoading();
	}
}

function handleError (e) {
	if (typeof e.response !== 'undefined' && typeof e.response.data !== 'undefined') {
		const response = JSON.parse(e.response.data);

		if (response && typeof response.message !== 'undefined') {
			throw new Error(response.message);
		}
	}

	throw new Error(e);
}