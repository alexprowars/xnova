<template>
	<Head :title="$t('pages.alliance.join.meta_title')"/>
	<div class="page-alliance ">
		<AllianceBack/>
		<header class="alliance-heading">
			<h1>{{ $t('pages.alliance.join.heading_title', [page.tag]) }}</h1>
		</header>
		<section v-if="page.text" class="alliance-panel">
			<h2>{{ $t('pages.alliance.join.alliance_welcome_heading') }}</h2>
			<div class="alliance-prose">
				<TextViewer :text="page.text"/>
			</div>
		</section>
		<section class="alliance-panel">
			<form class="alliance-form" @submit.prevent="send">
				<div class="alliance-field-heading">
					<label for="alliance-request">{{ $t('pages.alliance.ui.application') }}</label>
					<span>{{ form.message.length }} / 255</span>
				</div>
				<textarea id="alliance-request" rows="6" v-model="form.message" maxlength="255"></textarea>
				<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
				<div class="alliance-actions">
					<button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.join.submit_request') }}</button>
				</div>
			</form>
		</section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const { t } = useI18n();

	const form = useForm({
		message: '',
	});

	function send() {
		if (form.processing) return;

		form.post('/alliance/join/' + props.page['id'], {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.join.success_after_submit'));
			}
		});
	}
</script>