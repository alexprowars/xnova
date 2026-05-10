<template>
	<div v-if="resources" class="building-price">
		<template v-for="(value, resource) in price">
			<div v-if="value > 0" class="building-price-item">
				<img :src="'/assets/images/skin/s_'+resource+'.png'" v-tooltip="$t('resources.' + resource)" :alt="$t('resources.' + resource)">

				<span v-if="resources[resource]['value'] >= value" class="resYes">{{ $formatNumber(value) }}</span>
				<span v-else class="resNo" :v-tooltip="'Необходимо еще: '+$formatNumber(value - resources[resource]['value'])">{{ $formatNumber(value) }}</span>
			</div>
		</template>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';

	defineProps({
		price: {
			type: Object
		}
	});

	const state = useState();

	const resources = computed(() => {
		return state.planet.resources;
	});
</script>