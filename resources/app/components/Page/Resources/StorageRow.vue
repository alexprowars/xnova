<template>
	<div class="resources-storage-card">
		<div class="resources-status-heading">
			<span class="resources-label" :class="resource"><component :is="resourceIcons[resource]" aria-hidden="true" focusable="false"/>{{ $t('resources.' + resource) }}</span>
			<strong :class="{ 'negative': storage >= 100 }">{{ storage }}%</strong>
		</div>
		<ResourcesBar :value="storage" :aria-label="$t('resources.' + resource)"/>
		<div class="resources-storage-amount"><span>{{ $formatNumber(planet.resources[resource].value) }}</span><span>/ {{ $formatNumber(planet.resources[resource].capacity) }}</span></div>
	</div>
</template>

<script setup>
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import useState from '~/composables/useState.js';
	import ResourcesBar from '~/components/Page/Resources/Bar.vue';
	import { computed } from 'vue';

	const resourceIcons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
	};

	const props = defineProps({
		resource: String,
	});

	const state = useState();
	const planet = computed(() => state.planet);

	const storage = computed(() => Math.max(0, Math.floor((planet.value['resources'][props.resource]['value'] / planet.value['resources'][props.resource]['capacity']) * 100)));
</script>