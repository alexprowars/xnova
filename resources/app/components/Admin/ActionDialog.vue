<template>
	<dialog ref="dialog" class="admin-dialog" @cancel="form.processing && $event.preventDefault()">
		<form v-if="action" @submit.prevent="submit" class="space-y-5">
			<div>
				<h2>{{ action.title }}</h2>
				<p class="admin-muted mt-2">{{ action.description || labels.confirm_action }}</p>
			</div>
			<Fields :fields="action.fields || []" :form="form" compact />
			<ul v-if="Object.keys(form.errors).length" class="admin-error space-y-1" role="alert">
				<li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
			</ul>
			<div class="admin-toolbar justify-end">
				<button
					type="button"
					class="admin-button"
					@click="dialog.close()"
					:disabled="form.processing"
				>
					{{ labels.cancel }}
				</button>
				<button
					class="admin-button"
					:class="action.danger ? 'danger' : 'primary'"
					:disabled="form.processing"
				>
					{{ form.processing ? labels.processing : labels.confirm }}
				</button>
			</div>
		</form>
	</dialog>
</template>

<script setup>
	import { nextTick, ref, computed } from 'vue';
	import { useForm, usePage } from '@inertiajs/vue3';
	import Fields from './Fields.vue';
	const page = usePage();
	const labels = computed(() => page.props.admin.labels);
	const dialog = ref();
	const action = ref(null);
	const form = useForm({});
	async function open(config) {
		action.value = config;
		form.clearErrors();
		Object.keys(form.data()).forEach((key) => {
			delete form[key];
		});
		Object.entries(config.data || {}).forEach(([key, value]) => {
			form[key] = value;
		});
		await nextTick();
		dialog.value.showModal();
	}
	function submit() {
		form.transform(() =>
			Object.fromEntries(Object.keys(action.value.data || {}).map((key) => [key, form[key]])),
		).submit(action.value.method || 'post', action.value.url, {
			preserveScroll: true,
			onSuccess: () => dialog.value?.close(),
		});
	}
	defineExpose({ open });
</script>
