<template>
	<div :class="[appClass]">
		<slot/>

		<Loader v-if="loading"/>
		<ModalsContainer />
	</div>
</template>

<script setup>
	import { computed, provide } from 'vue';
	import { router, usePage, usePoll } from '@inertiajs/vue3';
	import { ModalsContainer } from 'vue-final-modal';
	import Loader from '~/components/Layout/Loader.vue';
	import useEcho from './composables/useEcho.js';
	import useChatStore from './store/useChatStore.js';
	import { setLocale } from './i18n.js';
	import dayjs from 'dayjs';
	import { closeModals } from './composables/useModals.js';

	defineProps({
		appClass: String,
		loading: {
			type: Boolean,
			default: false,
		}
	});

	const chatStore = useChatStore();
	const echo = useEcho();

	provide('echo', echo);
	provide('chat', chatStore);

	const page = usePage();
	const user = computed(() => page.props.user);

	setLocale(page.props.locale);
	dayjs.locale(page.props.locale);

	router.on('navigate', () => {
		closeModals();
	});

	if (user.value) {
		/*usePoll(60000, {
			only: ['user', 'planet', 'queue', 'stats']
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
				router.reload({
					only: ['user', 'planet', 'queue', 'stats']
				});
			});
	}
</script>