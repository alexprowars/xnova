<template>
	<Head :title="page.item.subject"/>
	<div class="page-support page-support-detail">
		<UiBackLink href="/support">{{ $t('pages.support.detail.back_to_list') }}</UiBackLink>
		<UiHeading class="support-heading"><div class="support-detail-heading"><span class="support-ticket-number">{{ $t('pages.support.detail.ticket', { id: page.item.id }) }}</span><h1>{{ page.item.subject }}</h1></div><span class="support-status" :class="'status-' + page.item.status">{{ $t('pages.support.status.' + page.item.status) }}</span></UiHeading>
		<div class="support-thread">
			<article class="support-message support-original"><div class="support-message-meta"><strong>{{ $t('pages.support.detail.original') }}</strong><time v-if="page.item.created_at" :datetime="page.item.created_at">{{ $formatDate(page.item.created_at, 'DD MMM YYYY HH:mm') }}</time></div><div class="support-message-body" v-html="page.item.message"/></article>
			<article v-for="(message, index) in page.item.messages" :key="index" class="support-message" :class="{ 'is-own': message.user_id === state.user.id }"><div class="support-message-meta"><a v-if="message.user_id" :href="'/players/' + message.user_id" target="_blank">{{ message.user }}</a><span v-else>{{ message.user || '—' }}</span><time v-if="message.date" :datetime="message.date">{{ $formatDate(message.date, 'DD MMM YYYY HH:mm') }}</time></div><div class="support-message-body" v-html="message.message"/></article>
		</div>
		<UiPanel clip :title="$t('pages.support.detail.answer_title')" v-if="page.item.status !== 0"><form class="ui-form-body" method="post" @submit.prevent="answer">
			<div class="support-editor-heading"><span>{{ $t('pages.support.form.message') }}</span><span :class="{ 'is-invalid': form.message.length > 255 }">{{ form.message.length }} / 255</span></div>
			<TextEditor name="message" v-model="form.message" :class="{error: v$.message.$error || form.errors.message}"/>
			<div v-if="v$.message.$error" class="ui-errors" role="alert">{{ $t(form.message.length > 255 ? 'pages.support.form.message_limit' : 'pages.support.form.message_required') }}</div>
			<div v-if="Object.keys(form.errors).length" class="ui-errors" role="alert"><span v-for="(error, field) in form.errors" :key="field">{{ error }}</span></div>
			<div class="ui-actions"><UiButton type="submit" :disabled="form.processing"><SendIcon aria-hidden="true"/>{{ $t('pages.support.detail.reply') }}</UiButton></div>
		</form></UiPanel>
		<div v-else class="support-closed">{{ $t('pages.support.detail.closed') }}</div>
	</div>
</template>

<script setup>
	import { UiBackLink, UiButton, UiHeading, UiPanel } from '~/components/UI';
	import SendIcon from '~/images/icons/send.svg?component';
	import useState from '~/composables/useState.js';
	import { maxLength, required } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';
	import { Head, useForm } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();
	const state = useState();

	defineOptions({ layout: { view: { resources: false } } });

	const props = defineProps({
		page: Object,
	});

	const form = useForm({
		message: '',
	});

	const validations = {
		message: {
			required,
			maxLength: maxLength(255),
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function answer () {
		if (form.processing || !await v$.value.$validate()) {
			return;
		}

		form.post('/support/' + props.page.item['id'] + '/answer', {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				form.resetAndClearErrors();
				v$.value.$reset();

				useSuccessNotification(t('pages.support.notifications.request_added'));
			}
		});
	}
</script>