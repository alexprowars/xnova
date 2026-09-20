<template>
	<section class="alliance-panel"><h2>{{ $t('pages.alliance.ui.add_relation') }}</h2>
		<form class="alliance-form" @submit.prevent="save">
			<div class="alliance-fields"><label>{{ $t('pages.alliance.ui.alliance') }}<select v-model="form.alliance"><option :value="null" disabled>{{ $t('pages.alliance.ui.choose_alliance') }}</option><option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }} [{{ item.tag }}]</option></select></label><label>{{ $t('pages.alliance.ui.relation') }}<select v-model="form.status"><option v-for="status in [1, 2, 3]" :key="status" :value="status">{{ $t('alliance.diplomacy_status.' + status) }}</option></select></label></div>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="submit" class="button" :disabled="!form.alliance || form.processing">{{ $t('pages.alliance.ui.add') }}</button></div>
		</form>
	</section>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';

	import { useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const { t } = useI18n();

	defineProps({
		items: Array,
	});

	const form = useForm({
		alliance: null,
		status: 1,
	});

	function save() {
		if (form.processing) return;

		form.post('/alliance/diplomacy/create', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.ui.relation_created'));

				form.reset();
			}
		});
	}
</script>