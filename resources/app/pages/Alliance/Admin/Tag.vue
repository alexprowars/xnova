<template>
	<Head :title="$t('pages.alliance.admin.main_heading')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack href="/alliance/admin"/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.admin.tag_form_title') }}</h1></header>
		<section class="alliance-panel"><form class="alliance-form" @submit.prevent="save">
			<label for="alliance-tag">{{ $t('pages.alliance.index.abbreviation') }}</label>
			<input id="alliance-tag" type="text" v-model="form.tag" maxlength="8">
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.admin.action_change') }}</button></div>
		</form></section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';

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

	const form = useForm({
		tag: props.page.tag,
	});

	const { t } = useI18n();

	function save() {
		if (form.processing) return;

		form.post('/alliance/admin/tag', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.admin.tag_change_success_notice'));
			}
		});
	}
</script>