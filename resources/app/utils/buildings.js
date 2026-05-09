import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function queueByType(type) {
	const page = usePage();

	return page.props.queue.filter((item) => item.planet_id === page.props.planet?.id && item.type === type);
}

export const emptyFieldsCount = computed(() => {
	const page = usePage();

	if (!page.props.planet) {
		return 0;
	}

	return page.props.planet.field_max - page.props.planet.field_used - queueByType('build').length;
});