<template>
	<div class="grid grid-cols-2 text-center">
		<div class="c middle">
			{{ $t('tech.' + item['item']) }} {{ item['level'] }}{{ item['mode'] === 1 ? $t('pages.building.queue_destruction') : '' }}
		</div>
		<div class="k" v-if="index === 0">
			<div v-if="time > 0" class="z">
				{{ $formatTime(time, ':', true) }}
				<br>
				<a @click.prevent="cancel">
					{{ $t('pages.building.queue_cancel') }}
				</a>
			</div>
			<div v-else class="z">
				{{ $t('pages.building.queue_finished') }}
				<br>
				<Link href="/buildings">
					{{ $t('pages.building.queue_next') }}
				</Link>
			</div>
			<div class="positive">{{ $formatDate(item['date'], 'DD MMM HH:mm:ss') }}</div>
		</div>
		<div class="k" v-else>
			<a @click.prevent="remove">{{ $t('pages.building.queue_remove') }}</a>
			<div class="positive">{{ $formatDate(item['date'], 'DD MMM HH:mm:ss') }}</div>
		</div>
	</div>
</template>

<script setup>
	import dayjs from 'dayjs';
	import { useNow } from '@vueuse/core';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '../../../composables/useModals.js';
	import { Link, useForm } from '@inertiajs/vue3';

	const props = defineProps({
		index: Number,
		item: Object
	});

	const { t } = useI18n();
	const now = useNow({ interval: 1000 });
	const time = computed(() => dayjs(props.item['date']).diff(now.value) / 1000);

	function remove () {
		openConfirmModal(
			t('pages.building.queue_title'),
			t('pages.building.remove_confirm_title', [t('tech.' + props.item['item']), props.item['level']]),
			[{
				title: t('pages.building.remove_confirm_close'),
			}, {
				title: t('pages.building.remove_confirm_action'),
				async handler() {
					useForm({ index: props.index })
						.post('/buildings/queue/remove', {
							preserveUrl: true,
							preserveScroll: true,
						});
				}
			}]
		)
	}

	function cancel () {
		openConfirmModal(
			t('pages.building.queue_title'),
			t('pages.building.cancel_confirm_title', [t('tech.' + props.item['item']), props.item['level']]),
			[{
				title: t('pages.building.cancel_confirm_close'),
			}, {
				title: t('pages.building.cancel_confirm_action'),
				async handler() {
					useForm({ index: props.index - 1 })
						.post('/buildings/queue/cancel', {
							preserveUrl: true,
							preserveScroll: true,
						});
				}
			}]
		);
	}
</script>