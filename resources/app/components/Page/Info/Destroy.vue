<template>
	<div class="block">
		<div class="title">{{ $t('pages.info.destroy.title') }} "{{ $t('tech.' + item) }}" {{ $t('pages.info.destroy.level') }} {{ data['level'] }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid">
					<div class="th">
						<build-row-price :price="data['resources']"/>
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<div class="mb-2">{{ $t('pages.info.destroy.demolition_time') }} {{ $formatTime(data['time']) }}</div>

						<button @click.prevent="destroyAction">{{ $t('pages.info.destroy.destroy') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import BuildRowPrice from './../Buildings/BuildRowPrice.vue'
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '../../../composables/useModals.js';

	const props = defineProps({
		data: Object,
		item: Number
	});

	const { t } = useI18n();

	function destroyAction () {
		openConfirmModal(
			null,
			t('pages.info.destroy.destroy_confirm') + ' <b>' + t('tech.' + props.item) + ' ' + props.data['level'] + ' ' + t('pages.info.destroy.level_short') + '.</b>?',
			[{
				title: t('pages.info.destroy.close'),
			}, {
				title: t('pages.info.destroy.destroy'),
				handler: async () => {
					await useApiPost('/buildings/build/destroy', {
						element: props.item,
					});

					await closeModals();

					if (useRoute().path.indexOf('/buildings') !== -1) {
						await refreshNuxtData();
					}
				}
			}]
		);
	}
</script>