<template>
	<Head :title="$t('pages.alliance.admin.main_heading')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack href="/alliance/admin"/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.admin.name_form_title') }}</h1></header>
		<section class="alliance-panel"><form class="alliance-form" @submit.prevent="save">
			<label for="alliance-name">{{ $t('pages.alliance.index.name') }}</label>
			<input id="alliance-name" type="text" v-model="form.name" maxlength="32">
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.admin.action_change') }}</button></div>
		</form></section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { Head, useForm } from '@inertiajs/vue3';
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

	const form = useForm({
		name: props.page.name,
	});

	const { t } = useI18n();

	function save() {
		if (form.processing) return;

		form.post('/alliance/admin/name', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.admin.name_change_success_notice'));
			}
		});
	}
</script>