<template>
	<div class="resource-panel-item-tooltip">
		<div class="resource-panel-item-tooltip-title">{{ $t('resources.' + type) }}</div>
		<div class="resource-panel-item-tooltip-info">
			<div>
				<div>{{ $t('resource_panel.efficiency') }}:</div>
				<div>{{ resource['factor'] * 100 }}%</div>
			</div>
			<div>
				<div>{{ $t('resource_panel.production') }}:</div>
				<div>{{ $formatNumber(resource['production']) }}</div>
			</div>
			<div>
				<div>{{ $t('resource_panel.capacity') }}:</div>
				<div>{{ $formatNumber(resource['capacity']) }}</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	defineProps({
		resource: {
			type: Object
		},
		type: {
			type: String,
			default: ''
		}
	})
</script>