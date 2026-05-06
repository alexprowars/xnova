import { progress, setLayoutProps } from '@inertiajs/vue3';

let loaderTimeout;

export const startLoading = (force = false) => {
	stopLoading();

	if (typeof force === true) {
		setLayoutProps({
			loading: true,
		});
	} else {
		loaderTimeout = setTimeout(() => {
			setLayoutProps({
				loading: true,
			});
		}, 500)
	}
}

export const stopLoading = () => {
	clearTimeout(loaderTimeout)

	setLayoutProps({
		loading: false,
	});
}

export const useWithLoadngIndicator = async (callback) => {
	progress.start();

	await callback();

	progress.finish();
};