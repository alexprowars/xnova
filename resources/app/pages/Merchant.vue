<template>
	<Head title="Торговец"/>
	<div class="block start">
		<div class="title">{{ $t('pages.merchant.title') }}</div>
		<div class="content">
			<form method="post" class="block-table text-center" @submit.prevent="exchange">
				<div class="grid" :class="{ 'grid-cols-2': type !== '' }">
					<div class="th">
						<div>{{ $t('pages.merchant.description_line_1') }}</div>
						<div class="negative">{{ $t('pages.merchant.description_line_2') }}</div>

						<select v-model="type" class="mt-4">
							<option value="">{{ $t('pages.merchant.select_resource') }}</option>
							<option value="metal">{{ $t('resources.metal') }}</option>
							<option value="crystal">{{ $t('resources.crystal') }}</option>
							<option value="deuterium">{{ $t('resources.deuterium') }}</option>
						</select>

						<div class="my-4">
							{{ $t('pages.merchant.rate_info', page.rate) }}
						</div>
					</div>
					<div v-if="type !== ''" class="th">
						<div class="block-table">
							<div class="grid">
								<div class="c">{{ $t('pages.merchant.exchange_for', [$t('resources.' + type)]) }}</div>
							</div>
							<div class="grid grid-cols-12">
								<div class="col-span-3 th"></div>
								<div class="col-span-3 th">{{ $t('pages.merchant.rate') }}</div>
								<div class="col-span-6 th"></div>
							</div>
							<div v-for="res in ['metal', 'crystal', 'deuterium']" class="grid grid-cols-12">
								<div class="col-span-3 th middle">{{ $t('resources.' + res) }}</div>
								<div class="col-span-3 th middle">{{ page.rate[res] / page.rate[type] }}</div>
								<div class="col-span-6 th middle">
									<Number v-if="type !== res" min="0" v-model="resources[res]" :placeholder="$t('pages.merchant.quantity')" @input="calculate"/>
									<span v-else>{{ resources[res] }}</span>
								</div>
							</div>
							<div class="grid">
								<div class="th negative">{{ $t('pages.merchant.warning_text', [1]) }}</div>
							</div>
							<div class="grid">
								<div class="c">
									<button type="submit" class="button">{{ $t('pages.merchant.exchange') }}</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import Number from '~/components/Number.vue';

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