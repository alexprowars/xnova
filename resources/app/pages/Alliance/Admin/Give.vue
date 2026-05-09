<template>
	<Head :title="$t('pages.alliance.admin.give_page_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.alliance.admin.give_page_title') }}</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="send">
				<div>
					<div class="th">
						{{ $t('pages.alliance.admin.give_transfer_player_label') }}
						<select v-model="form.user">
							<option value="">{{ $t('pages.alliance.admin.give_player_placeholder') }}</option>
							<option v-for="item in data['members']" :value="item['id']">{{ item['name'] }} [{{ item['rank'] }}]</option>
						</select>
					</div>
				</div>
				<div v-if="form.user">
					<div class="th">
						<button type="submit">{{ $t('pages.alliance.admin.give_submit_transfer') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/alliance" class="button">{{ $t('pages.alliance.admin.nav_back_alliance_root') }}</Link>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
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
		data: Object,
	});

	const form = useForm({
		user: null,
	});

	const { t } = useI18n();

	function send() {
		form.post('alliance/admin/give', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.admin.give_transfer_success_notice'));
			}
		});
	}
</script>