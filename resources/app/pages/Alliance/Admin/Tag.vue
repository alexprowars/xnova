<template>
	<Head :title="$t('pages.alliance.admin.main_heading')"/>
	<div class="block">
		<div class="title">{{ $t('pages.alliance.admin.tag_form_title') }}</div>
		<div class="content">
			<form method="post" @submit.prevent="save" class="block-table text-center">
				<div>
					<div class="th">
						<input type="text" v-model="form.tag">
					</div>
				</div>
				<div>
					<div class="c">
						<button type="submit" class="button">{{ $t('pages.alliance.admin.action_change') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/alliance/admin" class="button">{{ $t('pages.alliance.admin.nav_back_admin_hub') }}</Link>
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

	const props = defineProps({
		tag: String,
	});

	const form = useForm({
		tag: props.tag,
	});

	const { t } = useI18n();

	function save() {
		form.post('/alliance/admin/tag', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.admin.tag_change_success_notice'));
			}
		});
	}
</script>