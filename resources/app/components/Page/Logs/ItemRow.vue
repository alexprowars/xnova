<template>
	<tr>
		<td class="logs-number">{{ item.id }}</td>
		<th scope="row" class="logs-name">{{ item.title }}</th>
		<td class="logs-action-cell">
			<a :href="'/logs/' + item.id" target="_blank" rel="noopener" class="game-list-action button is-secondary">
				<svg
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					stroke-linecap="round"
					stroke-linejoin="round"
					aria-hidden="true"
				>
					<path d="M14 4h6v6m0-6L10 14M10 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-5"/>
				</svg>
				{{ $t('pages.logs.item.open') }}
			</a>
		</td>
		<td class="logs-action-cell">
			<button type="button" class="game-list-action button is-danger" @click="deleteItem">
				<svg
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="1.5"
					stroke-linecap="round"
					stroke-linejoin="round"
					aria-hidden="true"
				>
					<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5m4-5v5"/>
				</svg>
				{{ $t('pages.logs.item.delete') }}
			</button>
		</td>
	</tr>
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