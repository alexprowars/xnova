<template>
	<div class="block">
		<div class="title">
			{{ $t('pages.support.detail.subject_prefix') }} {{ item['subject'] }}
			<div class="float-end">{{ $t('pages.support.detail.status_prefix') }} {{ $t('pages.support.status.' + item['status']) }}</div>
		</div>
		<div class="content">
			<div class="block-table">
				<div class="grid">
					<div class="th text-left" v-html="item['message']"></div>
				</div>
				<div v-for="message in item['messages']" class="th">
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

	<div v-if="item['status'] !== 0" class="block mt-4">
		<div class="title">
			{{ $t('pages.support.detail.answer_title') }}
		</div>
		<div class="content">
			<div class="grid">
				<div class="th">
					<TextEditor v-model="message" :class="{error: v$.message.$error}"/>
				</div>
			</div>
			<div class="grid">
				<div class="c text-center">
					<button @click.prevent="answer">{{ $t('pages.support.detail.reply') }}</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { required } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';
	import { Link, router } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { openErrorModal } from '~/composables/useModals.js';
	import { useApiPost } from '~/composables/useApi.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();

	const props = defineProps({
		item: {
			type: Object,
		}
	});

	const message = ref('');

	const validations = {
		message: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		{ message },
		{ $autoDirty: true }
	);

	async function answer () {
		if (!await v$.value.$validate()) {
			return
		}

		try {
			await useApiPost('/support/' + props.item['id'] + '/answer', {
				message: message.value,
			});

			message.value = '';

			router.reload();
			useSuccessNotification(t('pages.support.notifications.request_added'));
		} catch (e) {
			openErrorModal(e);
		}
	}
</script>