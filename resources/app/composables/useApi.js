import { startLoading, stopLoading } from '~/composables/useLoading.js';
import { useErrorNotification } from '~/composables/useToast.js';

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