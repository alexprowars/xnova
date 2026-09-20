<template>
	<UiPanel clip :title="$t('pages.support.create.title')" class="support-create">
		<form class="ui-form-body" method="post" @submit.prevent="request">
			<label class="support-field"><span>{{ $t('pages.support.create.subject_placeholder') }}</span><input type="text" v-model="form.subject" :class="{error: v$.subject.$error || form.errors.subject}" :placeholder="$t('pages.support.create.subject_placeholder')"></label>
			<div v-if="v$.subject.$error" class="ui-errors" role="alert">{{ $t('pages.support.form.subject_required') }}</div>
			<div class="support-field-label">{{ $t('pages.support.form.message') }}</div>
			<TextEditor name="message" v-model="form.message" :class="{error: v$.message.$error || form.errors.message}"/>
			<div v-if="v$.message.$error" class="ui-errors" role="alert">{{ $t('pages.support.form.message_required') }}</div>
			<div v-if="Object.keys(form.errors).length" class="ui-errors" role="alert"><span v-for="(error, field) in form.errors" :key="field">{{ error }}</span></div>
			<div class="ui-actions"><UiButton variant="secondary" :disabled="form.processing" @click="emit('close')">{{ $t('pages.support.create.close') }}</UiButton><UiButton type="submit" :disabled="form.processing"><SendIcon aria-hidden="true"/>{{ $t('pages.support.create.send') }}</UiButton></div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import SendIcon from '~/images/icons/send.svg?component';
	import { required } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';
	import TextEditor from '~/components/TextEditor.vue';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useForm } from '@inertiajs/vue3';

	const emit = defineEmits(['close']);
	const { t } = useI18n();

	const form = useForm({
		message: '',
		subject: '',
	});

	const validations = {
		message: {
			required
		},
		subject: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function request() {
		if (form.processing || !await v$.value.$validate()) {
			return;
		}

		form.post('/support/create', {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification(t('pages.support.notifications.request_added'));

				emit('close');
			}
		});
	}
</script>