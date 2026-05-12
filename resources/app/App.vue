<template>
	<div>
		<slot/>

		<Loader v-if="loading"/>
		<ModalsContainer />
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed, provide, watch } from 'vue';
	import { router, usePoll } from '@inertiajs/vue3';
	import { ModalsContainer } from 'vue-final-modal';
	import Loader from '~/components/Layout/Loader.vue';
	import useEcho from './composables/useEcho.js';
	import useChatStore from './store/useChatStore.js';
	import { setLocale } from './i18n.js';
	import dayjs from 'dayjs';
	import { closeModals } from './composables/useModals.js';

	const props = defineProps({
		bodyClass: String,
		loading: {
			type: Boolean,
			default: false,
		}
	});

	const chatStore = useChatStore();
	const echo = useEcho();

	provide('echo', echo);
	provide('chat', chatStore);

	const state = useState();
	const user = computed(() => state.user);

	setLocale(state.locale);
	dayjs.locale(state.locale);

	router.on('navigate', () => {
		closeModals();
	});

	watch(() => props.bodyClass, (value, oldValue) => {
		if (oldValue) {
			document.querySelector('body').classList.remove(oldValue);
		}

		if (value) {
			document.querySelector('body').classList.add(value);
		}
	}, { immediate: true });

	if (user.value) {
		/*usePoll(60000, {
			only: ['state']
		});*/

		echo?.channel('chat')
			.listen('ChatMessage', ({ message }) => {
				chatStore.addMessage(message);
			});

		echo?.private('user.' + user.value.id)
			.listen('ChatPrivateMessage', ({ message }) => {
				chatStore.addMessage(message);
			})
			.listen('PlanetEntityUpdated', () => {
				//router.reload({
				//	only: ['state']
				//});
			});
	}
</script>