<template>
	<article class="officiers-item" :class="{ 'is-active': date, 'is-vacation': user.vacation }">
		<div class="officiers-item-header">
			<h2>{{ item.name }}</h2>
			<span v-if="date" class="officiers-status is-active">
				{{ $t('pages.overview.officier_active_until') }}: {{ $formatDate(date, 'DD MMM YYYY HH:mm:ss') }}
			</span>
			<span v-else class="officiers-status">{{ $t('pages.overview.officier_noactive') }}</span>
		</div>
		<div class="officiers-item-body">
			<div class="officiers-item-image">
				<img :src="'/assets/images/officiers/' + item.code + '.jpg'" :alt="item.name" width="112" height="112" loading="lazy">
			</div>
			<div class="officiers-item-description">
				<div class="officiers-biography" v-html="item.description"></div>
				<ul class="officiers-powers">
					<li v-for="power in item.power" :key="power">
						<CheckIcon stroke-width="1.5" aria-hidden="true"/>
						<span>{{ power }}</span>
					</li>
				</ul>
			</div>
			<div v-if="!user.vacation" class="officiers-item-action">
				<button
					v-for="contract in contracts"
					:key="contract.duration"
					type="button"
					class="button officiers-contract"
					@click="submit(contract.duration, contract.price)"
				>
					<span>{{ $t('pages.officiers.' + contract.label) }}</span>
					<span class="officiers-contract-price">{{ contract.price }} <span>{{ $t('pages.officiers.cost_credits') }}</span></span>
				</button>
			</div>
		</div>
	</article>
</template>

<script setup>
	import CheckIcon from '~/images/icons/check.svg?component';
	import { useI18n } from 'vue-i18n';
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useForm } from '@inertiajs/vue3';

	const { t } = useI18n();

	const props = defineProps({
		item: Object,
		contracts: Array,
	});

	const state = useState();
	const user = computed(() => state.user);

	const date = computed(() => user.value['officiers'].find((v) => v['code'] === props.item['code'])?.['date']);

	function submit (value, price) {
		openConfirmModal(
			t('officer_dialog.title'),
			t('officer_dialog.confirm', { name: props.item['name'], days: value, price }),
			[{
				title: t('officer_dialog.cancel'),
			}, {
				title: t('officer_dialog.hire'),
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
