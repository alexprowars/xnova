<template>
	<Head :title="$t('pages.alliance.admin.main_heading')"/>
	<div>
		<div class="block">
			<div class="title">{{ $t('pages.alliance.admin.main_heading') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div>
						<div class="th"><Link href="/alliance/admin/ranks">{{ $t('pages.alliance.admin.index_link_ranks') }}</Link></div>
					</div>
					<div v-if="data['access']['kick']">
						<div class="th"><Link href="/alliance/admin/members">{{ $t('pages.alliance.admin.index_link_members') }}</Link></div>
					</div>
					<div>
						<div class="th"><Link href="/alliance/admin/tag">{{ $t('pages.alliance.admin.index_link_change_tag') }}</Link></div>
					</div>
					<div>
						<div class="th"><Link href="/alliance/admin/name">{{ $t('pages.alliance.admin.index_link_change_name') }}</Link></div>
					</div>
				</div>
			</div>
		</div>

		<AllianceTextForm :data="data"/>
		<AllianceUpdateForm :data="data"/>

		<div class="block">
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-2">
						<div v-if="data['access']['delete'] || false">
							<div class="c">{{ $t('pages.alliance.admin.index_dissolve_caption') }}</div>
							<div class="th"><button @click.prevent="remove">{{ $t('pages.alliance.admin.action_continue') }}</button></div>
						</div>
						<div v-if="data['owner'] === user['id']">
							<div class="c">{{ $t('pages.alliance.admin.index_leave_transfer_caption') }}</div>
							<div class="th"><Link href="/alliance/admin/give" class="button">{{ $t('pages.alliance.admin.action_continue') }}</Link></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-2">
			<Link href="/alliance" class="button">{{ $t('pages.alliance.admin.nav_back_alliance_root') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import AllianceUpdateForm from '~/components/Page/Alliance/AllianceUpdateForm.vue';
	import AllianceTextForm from '~/components/Page/Alliance/AllianceTextForm.vue';
	import { Head, Link, router, usePage } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useApiSubmit } from '~/composables/useApi.js';
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
		data: Object,
	})

	const { t } = useI18n();

	const page = usePage();
	const user = computed(() => page.props.user);

	function remove() {
		openConfirmModal(
			null,
			t('pages.alliance.admin.index_dissolve_confirm_prompt'),
			[{
				title: t('pages.alliance.admin.confirm_decline'),
			}, {
				title: t('pages.alliance.admin.confirm_accept'),
				handler: () => {
					useApiSubmit('alliance/admin/remove', {}, () => {
						useSuccessNotification(t('pages.alliance.admin.index_dissolve_success_notice'));

						router.visit('/alliance');
					});
				}
			}]
		);
	}
</script>