<template>
	<article class="officiers-item" :class="{ 'is-active': date, 'is-vacation': user.vacation }">
		<div class="officiers-item-header">
			<h2>{{ item.name }}</h2>
			<span v-if="date" class="officiers-status is-active"><span class="officiers-status-dot" aria-hidden="true"></span>{{ $t('pages.overview.officier_active_until') }}: {{ $formatDate(date, 'DD MMM YYYY HH:mm:ss') }}</span>
			<span v-else class="officiers-status">{{ $t('pages.overview.officier_noactive') }}</span>
		</div>
		<div class="officiers-item-body">
			<div class="officiers-item-image"><img :src="'/assets/images/officiers/' + item.code + '.jpg'" :alt="item.name" width="112" height="112" loading="lazy"></div>
			<div class="officiers-item-description">
				<div class="officiers-biography" v-html="item.description"></div>
				<ul class="officiers-powers">
					<li v-for="power in item.power" :key="power"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg><span>{{ power }}</span></li>
				</ul>
			</div>
			<div v-if="!user.vacation" class="officiers-item-action">
				<button v-for="contract in contracts" :key="contract.duration" type="button" class="button officiers-contract" @click="submit(contract.duration, contract.price)">
					<span>{{ $t('pages.officiers.' + contract.label) }}</span>
					<span class="officiers-contract-price">{{ contract.price }} <span>{{ $t('pages.officiers.cost_credits') }}</span></span>
				</button>
			</div>
		</div>
	</article>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		item: Object,
	});

	const contracts = [
		{ duration: 7, price: 20, label: 'cost_week' },
		{ duration: 14, price: 40, label: 'cost_weeks' },
		{ duration: 30, price: 80, label: 'cost_month' },
	];

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