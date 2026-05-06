<template>
	<div class="resource-panel-item">
		<InfoPopup :id="building[type]" class="resource-panel-item-icon">
			<Popper>
				<span class="sprite" :class="['skin_' + type]"></span>
				<span class="sprite" :class="['skin_s_' + type]"></span>
				<template #content>
					<ResourceTooltip :resource="resource" :type="type"/>
				</template>
			</Popper>
		</InfoPopup>
		<div class="neutral">{{ $t('resources.' + type) }}</div>
		<div title="Количество ресурса на планете">
			<span :class="[resource.capacity > resource.value ? 'positive' : 'negative']">
				{{ $formatNumber(resource.value) }}
			</span>
		</div>
	</div>
</template>

<script setup>
	import ResourceTooltip from './PlanetPanelResourceTooltip.vue'
	import InfoPopup from '../Page/Info/Popup.vue'
	import { ref } from 'vue';
	import Popper from '../Popper.vue';

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