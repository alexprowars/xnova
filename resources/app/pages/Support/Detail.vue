<template>
	<div class="block">
		<div class="title">
			{{ $t('pages.support.detail.subject_prefix') }} {{ page.item['subject'] }}
			<div class="float-end">{{ $t('pages.support.detail.status_prefix') }} {{ $t('pages.support.status.' + page.item['status']) }}</div>
		</div>
		<div class="content">
			<div class="block-table">
				<div class="grid">
					<div class="th text-left" v-html="page.item['message']"></div>
				</div>
				<div v-for="message in page.item['messages']" class="th">
					<div class="positive">
						{{ $formatDate(message['date'], 'DD.MM.YYYY HH:mm') }} {{ $t('pages.support.detail.message_from') }}
						<a :href="'/players/' + message['user_id']" target="_blank">{{ message['user'] }}</a>
					</div>
					<div class="mt-2" v-html="message['message']"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="mt-2">
		<Link href="/support" class="button">{{ $t('pages.support.detail.back') }}</Link>
	</div>

	<div v-if="page.item['status'] !== 0" class="block mt-4">
		<div class="title">
			{{ $t('pages.support.detail.answer_title') }}
		</div>
		<div class="content">
			<div class="grid">
				<div class="th">
					<TextEditor v-model="form.message" :class="{error: v$.message.$error}"/>
				</div>
			</div>
			<div class="grid">
				<div class="c text-center">
					<button class="button" @click.prevent="answer">{{ $t('pages.support.detail.reply') }}</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { required } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';
	import { Link, useForm } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();

	const props = defineProps({
		page: Object,
	});

	const form = useForm({
		message: '',
	});

	const validations = {
		message: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function answer () {
		if (!await v$.value.$validate()) {
			return
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
