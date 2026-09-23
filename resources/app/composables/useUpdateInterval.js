import { createSharedComposable, useDocumentVisibility, useIntervalFn, useNow } from '@vueuse/core';
import { onScopeDispose, watch } from 'vue';

const intervals = new Map();

function createUpdateInterval(intervalMs) {
	return createSharedComposable(() => {
		onScopeDispose(() => intervals.delete(intervalMs));

		return useNow({
			scheduler: (update) => {
				const visibility = useDocumentVisibility();
				const interval = useIntervalFn(update, intervalMs, {
					immediate: visibility.value === 'visible',
				});

				watch(visibility, (state) => {
					if (state === 'visible') {
						update();
						interval.resume();
					} else {
						interval.pause();
					}
				}, { flush: 'sync' });

				return interval;
			},
		});
	});
}

export function useUpdateInterval(intervalMs = 1000) {
	if (!intervals.has(intervalMs)) {
		intervals.set(intervalMs, createUpdateInterval(intervalMs));
	}

	return intervals.get(intervalMs)();
}