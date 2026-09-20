<template>
	<Head :title="$t('pages.alliance.admin.give_page_title')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack href="/alliance/admin"/>
		<header class="alliance-heading">
			<h1>{{ $t('pages.alliance.admin.give_page_title') }}</h1>
		</header>
		<section class="alliance-panel">
			<form class="alliance-form" @submit.prevent="send">
				<label for="alliance-successor">{{ $t('pages.alliance.admin.give_transfer_player_label') }}</label>
				<select id="alliance-successor" v-model="form.member">
					<option :value="null" disabled>{{ $t('pages.alliance.admin.give_player_placeholder') }}</option>
					<option v-for="item in page.members" :key="item.id" :value="item.id">{{ item.name }} [{{ item.rank }}]</option>
				</select>
				<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
				<div class="alliance-actions">
					<button type="submit" class="button is-danger" :disabled="!form.member || form.processing">
						{{ $t('pages.alliance.admin.give_submit_transfer') }}
					</button>
				</div>
			</form>
		</section>
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

	defineProps({
		page: Object,
	});

	const form = useForm({
		member: null,
	});

	const { t } = useI18n();

	function send() {
		if (form.processing) return;

		form.post('/alliance/admin/give', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.admin.give_transfer_success_notice'));
			}
		});
	}
</script>