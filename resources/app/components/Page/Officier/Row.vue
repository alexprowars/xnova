<template>
	<div class="officiers-item">
		<div class="officiers-item-title">
			{{ item['name'] }}
			<span v-if="date" class="positive">({{ $t('pages.overview.officier_active_until') }}: {{ $formatDate(date, 'DD MMM YYYY HH:mm:ss') }})</span>
			<span v-else class="negative">({{ $t('pages.overview.officier_noactive') }})</span>
		</div>
		<div class="flex flex-wrap sm:flex-nowrap gap-y-2 sm:gap-x-2">
			<div class="basis-1/2 sm:basis-1/6 grow order-1 sm:order-0 officiers-item-image">
				<img :src="'/assets/images/officiers/' + item['code'] + '.jpg'" align="top" alt="">
			</div>
			<div class="basis-full sm:basis-4/6 grow text-left officiers-item-description">
				<div v-html="item['description']"></div>
				<div class="flex my-4 gap-2">
					<div>
						<img :src="'/assets/images/officiers/' + item['code'] + '.gif'" :alt="item['name']">
					</div>
					<div class="flex flex-col justify-center gap-1">
						<div v-for="power in item['power']" class="text-sky-300">{{ power }}</div>
					</div>
				</div>
			</div>
			<div v-if="!user.vacation" class="basis-1/2 sm:basis-1/6 order-2 text-center officiers-item-action flex items-center justify-center">
				<div class="flex flex-col gap-2">
					<div>
						<button class="button" @click.prevent="submit(7, 20)">{{ $t('pages.officiers.cost_week') }}</button>
						<br>{{ $t('pages.officiers.cost') }}:&nbsp;<span class="positive">20</span>&nbsp;{{ $t('pages.officiers.cost_credits') }}
					</div>
					<div>
						<button class="button" @click.prevent="submit(14, 40)">{{ $t('pages.officiers.cost_weeks') }}</button>
						<br>{{ $t('pages.officiers.cost') }}:&nbsp;<span class="positive">40</span>&nbsp;{{ $t('pages.officiers.cost_credits') }}
					</div>
					<div>
						<button class="button" @click.prevent="submit(30, 80)">{{ $t('pages.officiers.cost_month') }}</button>
						<br>{{ $t('pages.officiers.cost') }}:&nbsp;<span class="positive">80</span>&nbsp;{{ $t('pages.officiers.cost_credits') }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		item: Object,
	});

	const state = useState();
	const user = computed(() => state.user);

	const date = computed(() => user.value['officiers'].find((v) => v['code'] === props.item['code'])?.['date']);

	function submit (value, price) {
		openConfirmModal(
			'Вербовка офицера',
			'Вы действительно хотите нанять офицера "<b>' + props.item['name'] + '</b>" на <b>' + value + '</b> дней за <b>' + price + '</b> кредитов?',
			[{
				title: 'Отменить',
			}, {
				title: 'Нанять',
				handler() {
					useForm({
						code: props.item['code'],
						duration: value
					})
					.post('/officiers/buy', {
						preserveUrl: true,
						preserveScroll: true,
					});
				}
			}]
		);
	}
</script>