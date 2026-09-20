<template>
	<section class="alliance-panel">
		<header>
			<h2>{{ $t('pages.alliance.ui.request_from', [request.name]) }}</h2>
			<button type="button" class="button is-secondary" @click="emit('close')">{{ $t('pages.alliance.ui.close') }}</button>
		</header>
		<div class="alliance-request-text">{{ request.message || '—' }}</div>
		<form class="alliance-form" @submit.prevent="accept">
			<label for="alliance-request-reply">{{ $t('pages.alliance.ui.reply') }}</label>
			<textarea id="alliance-request-reply" v-model="form.message" rows="4"></textarea>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions">
				<button type="button" class="button is-danger" :disabled="form.processing" @click="reject">
					{{ $t('pages.alliance.ui.reject') }}
				</button>
				<button type="submit" class="button is-success" :disabled="form.processing">{{ $t('pages.alliance.ui.accept') }}</button>
			</div>
		</form>
	</section>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';

	import { useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const { t } = useI18n();

	const props = defineProps({
		request: Object,
	});

	const form = useForm({
		id: props.request['id'],
		message: '',
	});

	const emit = defineEmits(['close']);

	function accept() {
		if (form.processing) return;

		form.post('/alliance/admin/requests/accept', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.ui.accepted'));

				emit('close');
			}
		});
	}

	function reject() {
		if (form.processing) return;

		form.post('/alliance/admin/requests/reject', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.ui.rejected'));

				emit('close');
			}
		});
	}
</script>