import { usePage } from '@inertiajs/vue3';
import { computed, hasInjectionContext, inject, reactive } from 'vue';

export const StateSymbol = Symbol('state');

export function createState () {
	const page = usePage();
	const props = computed(() => page.props.state || {});

	return reactive({
		messages: computed(() => props.value.messages || []),
		speed: computed(() => props.value.speed || {}),
		locale: computed(() => props.value.locale),
		stats: computed(() => props.value.stats || {}),
		user: computed(() => props.value.user),
		planet: computed(() => props.value.planet),
		queue: computed(() => props.value.queue || []),
		version: computed(() => props.value.version),
	});
}

export default function useState () {
	const state = hasInjectionContext()
		? inject(StateSymbol, null) : null;

	if (state) {
		return state;
	}

	return createState();
}
