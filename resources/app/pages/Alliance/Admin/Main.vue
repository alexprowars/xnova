<template>
	<Head :title="$t('pages.alliance.admin.main_heading')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.admin.main_heading') }}</h1></header>
		<nav class="alliance-admin-links">
			<Link href="/alliance/admin/ranks">{{ $t('pages.alliance.admin.index_link_ranks') }}<span aria-hidden="true">→</span></Link>
			<Link v-if="page.access.kick" href="/alliance/admin/members">{{ $t('pages.alliance.admin.index_link_members') }}<span aria-hidden="true">→</span></Link>
			<Link href="/alliance/admin/tag">{{ $t('pages.alliance.admin.index_link_change_tag') }}<span aria-hidden="true">→</span></Link>
			<Link href="/alliance/admin/name">{{ $t('pages.alliance.admin.index_link_change_name') }}<span aria-hidden="true">→</span></Link>
		</nav>
		<AllianceTextForm :key="page.text_type" :data="page"/>
		<AllianceUpdateForm :data="page"/>
		<div v-if="page.access.delete || page.owner === user.id" class="alliance-danger-zone">
			<div v-if="page.access.delete"><span>{{ $t('pages.alliance.admin.index_dissolve_caption') }}</span><button type="button" class="button is-danger" @click="remove">{{ $t('pages.alliance.ui.dissolve') }}</button></div>
			<div v-if="page.owner === user.id"><span>{{ $t('pages.alliance.admin.index_leave_transfer_caption') }}</span><Link href="/alliance/admin/give" class="button is-secondary">{{ $t('pages.alliance.admin.give_page_title') }}</Link></div>
		</div>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import useState from '~/composables/useState.js';
	import AllianceUpdateForm from '~/components/Page/Alliance/AllianceUpdateForm.vue';
	import AllianceTextForm from '~/components/Page/Alliance/AllianceTextForm.vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	})

	const { t } = useI18n();

	const state = useState();
	const user = computed(() => state.user);

	function remove() {
		openConfirmModal(
			null,
			t('pages.alliance.admin.index_dissolve_confirm_prompt'),
			[{
				title: t('pages.alliance.admin.confirm_decline'),
			}, {
				title: t('pages.alliance.admin.confirm_accept'),
				handler() {
					useForm().delete('/alliance/admin/remove', {
						preserveUrl: true,
						onSuccess() {
							useSuccessNotification(t('pages.alliance.admin.index_dissolve_success_notice'));
						}
					});
				}
			}]
		);
	}
</script>