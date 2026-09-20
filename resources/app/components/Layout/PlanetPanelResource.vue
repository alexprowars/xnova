<template>
	<div class="resource-panel-item" :class="'resource-' + type">
		<ModalLink navigate :href="'/info/' + building[type]" class="resource-panel-item-icon" :aria-label="$t('resources.' + type)">
			<Popper>
				<component :is="resourceIcons[type]" aria-hidden="true" focusable="false"/>
				<template #content>
					<ResourceTooltip :resource="resource" :type="type"/>
				</template>
			</Popper>
		</ModalLink>
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
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import ResourceTooltip from './PlanetPanelResourceTooltip.vue'
	import { ref } from 'vue';
	import Popper from '~/components/Popper.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	const resourceIcons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
	};

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