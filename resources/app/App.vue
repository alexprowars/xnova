<template>
	<div>
		<slot/>

		<Loader v-if="loading"/>
		<ModalTarget group="default" class="dialog-target">
			<ModalOverlay class="dialog-overlay"/>
		</ModalTarget>
	</div>
</template>

<script setup>
	import useState, { updateState } from '~/composables/useState.js';
	import { computed, provide, watch } from 'vue';
	import { router } from '@inertiajs/vue3';
	import { ModalOverlay, ModalTarget } from '@kolirt/vue-modal';
	import Loader from '~/components/Layout/Loader.vue';
	import useEcho from './composables/useEcho.js';
	import useChatStore from './store/useChatStore.js';
	import { useI18n } from 'vue-i18n';
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
	const { locale } = useI18n();
	const user = computed(() => state.user);

	watch(() => state.locale, (value) => {
		locale.value = value;
		dayjs.locale(value);

		if (typeof document !== 'undefined') {
			document.documentElement.lang = value;
		}
	});

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

	let updateStateTimer;

	function stateUpdate() {
		clearTimeout(updateStateTimer);
		updateState();
		updateStateTimer = setTimeout(stateUpdate, 60000);
	}

	if (user.value) {
		updateStateTimer = setTimeout(stateUpdate, 60000);

		echo?.channel('chat')
			.listen('ChatMessage', ({ message }) => {
				chatStore.addMessage(message);
			});

		echo?.private('user.' + user.value.id)
			.listen('ChatPrivateMessage', ({ message }) => {
				chatStore.addMessage(message);
			})
			.listen('PlanetEntityUpdated', () => {
				stateUpdate();
			});
	}
</script>