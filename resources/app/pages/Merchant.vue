<template>
	<Head :title="$t('menu.merchant')"/>
	<div class="page-merchant block">
		<div class="title">{{ $t('pages.merchant.title') }}</div>
		<div class="content">
			<form method="post" class="merchant-form" @submit.prevent="exchange">
				<div class="merchant-intro">
					<div class="merchant-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h15l-4-4M20 16H5l4 4M19 8l-4 4M5 16l4-4"/></svg></div>
					<div>
						<div class="merchant-description">{{ $t('pages.merchant.description_line_1') }}</div>
						<div class="merchant-fee"><CreditsIcon aria-hidden="true" focusable="false"/>{{ $t('pages.merchant.description_line_2') }}</div>
					</div>
				</div>

				<fieldset class="merchant-selection">
					<legend>{{ $t('pages.merchant.select_resource') }}</legend>
					<div class="merchant-options">
						<label v-for="res in ['metal', 'crystal', 'deuterium']" :key="res" class="merchant-option" :class="[res, { 'is-selected': type === res }]">
							<input type="radio" name="resource" :value="res" v-model="type" @change="calculate">
							<component :is="resourceIcons[res]" aria-hidden="true" focusable="false"/>
							<span>{{ $t('resources.' + res) }}</span>
						</label>
					</div>
					<div class="merchant-rate-note">{{ $t('pages.merchant.rate_info', page.rate) }}</div>
				</fieldset>

				<div v-if="type !== ''" class="merchant-exchange">
					<div class="merchant-exchange-heading">{{ $t('pages.merchant.exchange_for', [$t('resources.' + type)]) }}</div>
					<div class="merchant-table">
						<div class="merchant-table-heading" aria-hidden="true"><span></span><span>{{ $t('pages.merchant.rate') }}</span><span>{{ $t('pages.merchant.amount') }}</span></div>
						<div v-for="res in ['metal', 'crystal', 'deuterium']" :key="res" class="merchant-row" :class="{ 'is-source': type === res }">
							<label :for="'merchant-' + res" class="merchant-resource" :class="res"><component :is="resourceIcons[res]" aria-hidden="true" focusable="false"/><span>{{ $t('resources.' + res) }}<small>{{ $t(type === res ? 'pages.merchant.give' : 'pages.merchant.receive') }}</small></span></label>
							<div class="merchant-rate">{{ page.rate[res] / page.rate[type] }}</div>
							<div class="merchant-quantity">
								<Number v-if="type !== res" :id="'merchant-' + res" min="0" v-model="resources[res]" :placeholder="$t('pages.merchant.quantity')" @input="calculate"/>
								<output v-else :id="'merchant-' + res">{{ resources[res] }}</output>
							</div>
						</div>
					</div>
					<div class="merchant-actions"><button type="submit" class="button">{{ $t('pages.merchant.exchange') }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6"/></svg></button></div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import CreditsIcon from '~/images/icons/resources/credits.svg?component';
	import Number from '~/components/Number.vue';

	const resourceIcons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
	};

	const props = defineProps({
		page: Object,
	});

	const type = ref('');
	const resources = ref({ metal: 0, crystal: 0, deuterium: 0 });

	function calculate () {
		let res = 0;

		['metal', 'crystal', 'deuterium'].forEach((item) => {
			if (type.value !== item) {
				res += resources.value[item] * (props.page.rate[item] / props.page.rate[type.value]);
			}
		});

		resources.value[type.value] = res;
	}

	function exchange() {
		useForm({
			type: type.value,
			...resources.value,
		})
		.post('/merchant/exchange', {
			preserveUrl: true,
			onSuccess() {
				type.value = '';
			}
		});
	}
</script>