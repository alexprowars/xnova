<template>
	<div class="build-queue-row" :class="{ 'is-current': index === 0, 'is-finished': index === 0 && time <= 0 }">
		<div class="build-queue-item">
			<img class="build-queue-image" :src="'/assets/images/elements/' + item.item + '.webp'" alt="" width="42" height="42">
			<div class="build-queue-description">
				<div class="build-queue-name">
					<strong>{{ $t('tech.' + item.item) }}</strong>
					<span class="build-queue-level">{{ $t('pages.building.queue_level', { level: item.level }) }}</span>
				</div>
				<div class="build-queue-status">
					<span v-if="index === 0">
						{{ time > 0 ? $t('pages.building.queue_in_progress') : $t('pages.building.queue_finished') }}
					</span>
					<span v-else>{{ $t('pages.building.queue_pending') }} · {{ index + 1 }}</span>
					<span v-if="item.mode === 1" class="build-queue-demolition">{{ $t('pages.building.queue_dismantling') }}</span>
				</div>
			</div>
		</div>
		<div class="build-queue-details">
			<div class="build-queue-time">
				<div v-if="index === 0 && time > 0" class="build-queue-timer">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
						<circle cx="12" cy="12" r="9"/>
						<path d="M12 7v5l3 2"/>
					</svg>
					{{ $formatTime(time, ':', true) }}
				</div>
				<span v-else class="build-queue-time-label">{{ $t('pages.building.queue_completion') }}</span>
				<div class="build-queue-date" :title="$t('pages.building.queue_completion')">
					{{ $formatDate(item.date, 'DD MMM HH:mm:ss') }}
				</div>
			</div>
			<button v-if="index === 0 && time > 0" type="button" class="button is-danger" @click="cancel">
				{{ $t('pages.building.queue_cancel') }}
			</button>
			<Link v-else-if="index === 0" href="/buildings" class="button is-success">{{ $t('pages.building.queue_next') }}</Link>
			<button v-else type="button" class="button is-danger" @click="remove">{{ $t('pages.building.queue_remove') }}</button>
		</div>
	</div>
</template>

<script setup>
	import dayjs from 'dayjs';
	import { useUpdateInterval } from '~/composables/useUpdateInterval.js';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { Link, useForm } from '@inertiajs/vue3';

	const props = defineProps({
		index: Number,
		item: Object
	});

	const { t } = useI18n();
	const now = useUpdateInterval();
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
					useForm({ queue_id: props.item.id })
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
					useForm({ queue_id: props.item.id })
						.post('/buildings/queue/cancel', {
							preserveUrl: true,
							preserveScroll: true,
						});
				}
			}]
		);
	}
</script>