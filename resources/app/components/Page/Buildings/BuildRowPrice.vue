<template>
	<div v-if="resources" class="building-price">
		<template v-for="(value, resource) in price">
			<div v-if="value > 0" class="building-price-item">
				<Popper :content="$t('resources.' + resource)">
					<ResourceIcon :code="resource" class="building-resource-icon" :class="'resource-' + resource" role="img" :aria-label="$t('resources.' + resource)" focusable="false"/>
				</Popper>

				<span v-if="resources[resource]['value'] >= value" class="resYes">{{ $formatNumber(value) }}</span>
				<Popper v-else :content="$t('construction.resources_missing', { amount: $formatNumber(value - resources[resource]['value']) })">
					<span class="resNo">{{ $formatNumber(value) }}</span>
				</Popper>
			</div>
		</template>
	</div>
</template>

<script setup>
	import Popper from '~/components/Popper.vue';
	import ResourceIcon from '~/components/ResourceIcon.vue';
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