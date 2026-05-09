<template>
	<div class="grid grid-cols-12">
		<div class="col-span-1 th middle">{{ item['id'] }}</div>
		<div class="col-span-7 th middle">{{ item['title'] }}</div>
		<div class="col-span-2 th middle">
			<a :href="'/logs/' + item['id']" target="_blank">{{ $t('pages.logs.item.open') }}</a>
		</div>
		<div class="col-span-2 th middle">
			<a href="" class="button" @click.prevent="deleteItem">{{ $t('pages.logs.item.delete') }}</a>
		</div>
	</div>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { router } from '@inertiajs/vue3';

	const { t } = useI18n();

	const { item } = defineProps({
		item: Object
	});

	function deleteItem() {
		openConfirmModal(
			null,
			t('pages.logs.item.delete_confirm.title'),
			[{
				title: t('pages.logs.item.delete_confirm.yes'),
				handler: () => {
					useApiSubmit('/logs/' + item['id'], {
						_method: 'DELETE',
					}, () => {
						useSuccessNotification(t('pages.logs.item.delete_confirm.success'));

						router.reload();
					});
				}
			}, {
				title: t('pages.logs.item.delete_confirm.no'),
			}]
		);
	}
</script>