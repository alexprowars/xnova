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
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useForm } from '@inertiajs/vue3';

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
				handler() {
					useForm().delete('/logs/' + item['id'], {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.logs.item.delete_confirm.success'));
						}
					});
				}
			}, {
				title: t('pages.logs.item.delete_confirm.no'),
			}]
		);
	}
</script>