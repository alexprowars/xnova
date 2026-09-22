<template>
	<div class="resource-panel-item" :class="'resource-' + type">
		<Popper>
			<ModalLink navigate :href="'/info/' + building[type]" class="resource-panel-item-icon" :aria-label="$t('resources.' + type)">
				<ResourceIcon :code="type" aria-hidden="true" focusable="false"/>
			</ModalLink>
			<template #content>
				<ResourceTooltip :resource="resource" :type="type"/>
			</template>
		</Popper>
		<div class="resource-panel-item-info">
			<div class="resource-panel-item-label">{{ $t('resources.' + type) }}</div>
			<div class="resource-panel-item-value" :class="{ 'is-full': resource.value >= resource.capacity }" :title="$formatNumber(resource.value) + ' / ' + $formatNumber(resource.capacity)">
				{{ $formatNumber(resource.value) }}
			</div>
		</div>
		<div class="resource-panel-meter" aria-hidden="true" :class="{ 'is-full': resource.value >= resource.capacity }">
			<span :style="{ width: Math.min(100, Math.max(0, resource.capacity > 0 ? resource.value / resource.capacity * 100 : 100)) + '%' }"></span>
		</div>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import ResourceTooltip from './PlanetPanelResourceTooltip.vue'
	import { ref } from 'vue';
	import Popper from '~/components/Popper.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineProps({
		resource: {
			type: Object
		},
		type: {
			type: String,
			default: ''
		}
	})

	const building = ref({
		metal: 1,
		crystal: 2,
		deuterium: 3,
	});
</script>