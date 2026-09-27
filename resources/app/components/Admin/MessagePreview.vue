<template>
	<button type="button" class="admin-preview-button" @click="open">{{ labels.view }}</button>
	<dialog ref="dialog" class="admin-dialog admin-message-dialog" :aria-label="labels.message">
		<header class="flex items-center justify-between gap-4 mb-5">
			<h2>{{ message?.subject || labels.message }}</h2>
			<ActionButton icon="close" :label="labels.close" @click="dialog.close()" />
		</header>
		<p v-if="loading" class="admin-muted" role="status">{{ labels.processing }}</p>
		<p v-else-if="error" class="admin-error" role="alert">{{ error }}</p>
		<TextViewer v-else-if="message" :text="message.text" class="admin-message-body" />
	</dialog>
</template>

<script setup>
	import { computed, ref, watch } from 'vue';
	import { usePage } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import ActionButton from './ActionButton.vue';

	const props = defineProps({ url: String });
	const page = usePage();
	const labels = computed(() => page.props.admin.labels);
	const dialog = ref();
	const message = ref(null);
	const loading = ref(false);
	const error = ref('');

	watch(
		() => [props.url, page.props.locale],
		() => {
			message.value = null;
		},
	);

	async function open() {
		dialog.value.showModal();
		if (message.value || loading.value) {
			return;
		}

		loading.value = true;
		error.value = '';
		try {
			const response = await fetch(props.url, { headers: { Accept: 'application/json' } });
			if (!response.ok) {
				throw new Error(labels.value.message_load_failed);
			}
			message.value = await response.json();
		} catch {
			error.value = labels.value.message_load_failed;
		} finally {
			loading.value = false;
		}
	}
</script>
