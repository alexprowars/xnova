<template>
	<tr>
		<td class="logs-number">{{ item.id }}</td>
		<th scope="row" class="logs-name">{{ item.title }}</th>
		<td class="logs-action-cell">
			<a :href="'/logs/' + item.id" target="_blank" rel="noopener" class="game-list-action button is-secondary">
				<ExternalLinkIcon stroke-width="1.5" aria-hidden="true"/>
				{{ $t('pages.logs.item.open') }}
			</a>
		</td>
		<td class="logs-action-cell">
			<button type="button" class="game-list-action button is-danger" @click="deleteItem">
				<TrashIcon stroke-width="1.5" aria-hidden="true"/>
				{{ $t('pages.logs.item.delete') }}
			</button>
		</td>
	</tr>
</template>

<script setup>
	import ExternalLinkIcon from '~/images/icons/external-link.svg?component';
	import TrashIcon from '~/images/icons/trash.svg?component';
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