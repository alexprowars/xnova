<template>
	<div class="resources-storage-card">
		<div class="resources-status-heading">
			<span class="resources-label" :class="resource">
				<ResourceIcon :code="resource" aria-hidden="true" focusable="false"/>
				{{ $t('resources.' + resource) }}
			</span>
			<strong :class="{ 'negative': storage >= 100 }">{{ storage }}%</strong>
		</div>
		<ResourcesBar :value="storage" :aria-label="$t('resources.' + resource)"/>
		<div class="resources-storage-amount">
			<span>{{ $formatNumber(planet.resources[resource].value) }}</span>
			<span>/ {{ $formatNumber(planet.resources[resource].capacity) }}</span>
		</div>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import ResourcesBar from '~/components/Page/Resources/Bar.vue';
	import { computed } from 'vue';

	const props = defineProps({
		resource: String,
	});

	const state = useState();
	const planet = computed(() => state.planet);

	const storage = computed(() => Math.max(0, Math.floor((planet.value['resources'][props.resource]['value'] / planet.value['resources'][props.resource]['capacity']) * 100)));
</script>