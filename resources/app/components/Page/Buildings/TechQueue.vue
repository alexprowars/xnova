<template>
	<div class="building-info-upgrade-timer">
		<span v-if="time > 0">
			{{ $formatTime(time, ':', true) }}&nbsp;<a @click.prevent="cancelAction">{{ $t('pages.research.queue_cancel') }}<span v-if="build.name">{{ $t('pages.research.queue_cancel_on') }} {{ build.name }}</span></a>
		</span>
		<a v-else href="" @click.prevent="router.reload()">{{ $t('pages.research.queue_finished') }}</a>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import dayjs from 'dayjs';
	import { useNow } from '@vueuse/core';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { router, useForm } from '@inertiajs/vue3';

	const props = defineProps({
		build: Object,
	});

	const { t } = useI18n();
	const now = useNow({ interval: 1000 });
	const time = computed(() => dayjs(props.build['date']).diff(dayjs(now.value).utc()) / 1000);

	function cancelAction () {
		openConfirmModal(
			null,
			t('pages.research.cancel_confirm_title', [t('tech.' + props.build['item']), props.build['level']]),
			[{
				title: t('pages.research.cancel_confirm_close'),
			}, {
				title: t('pages.research.cancel_confirm_action'),
				async handler() {
					useForm({
						element: props.build['item'],
					})
					.post('/research/cancel', {
						preserveUrl: true,
					});
				}
			}]
		);
	}
</script>