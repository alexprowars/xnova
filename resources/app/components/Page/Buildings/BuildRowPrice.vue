<template>
	<div v-if="resources" class="building-price">
		<template v-for="(value, resource) in price">
			<div v-if="value > 0" class="building-price-item">
				<component :is="resourceIcons[resource]" class="building-resource-icon" :class="'resource-' + resource" v-tooltip="$t('resources.' + resource)" role="img" :aria-label="$t('resources.' + resource)" focusable="false"/>

				<span v-if="resources[resource]['value'] >= value" class="resYes">{{ $formatNumber(value) }}</span>
				<span v-else class="resNo" :v-tooltip="'Необходимо еще: '+$formatNumber(value - resources[resource]['value'])">{{ $formatNumber(value) }}</span>
			</div>
		</template>
	</div>
</template>

<script setup>
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import EnergyIcon from '~/images/icons/resources/energy.svg?component';
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';

	const resourceIcons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
		energy: EnergyIcon,
	};

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