import { computed } from 'vue';
import useState from '~/composables/useState.js';

export function queueByType(type) {
	const state = useState();

	return state.queue.filter((item) => item.planet_id === state.planet?.id && item.type === type);
}

export const emptyFieldsCount = computed(() => {
	const state = useState();

	if (!state.planet) {
		return 0;
	}

	return state.planet.field_max - state.planet.field_used - queueByType('build').length;
});
