<template>
	<div class="resource-panel-item-tooltip">
		<div class="resource-panel-item-tooltip-title">{{ $t('resources.' + type) }}</div>
		<div class="resource-panel-item-tooltip-info">
			<div>
				<div>Мощность:</div>
				<div>{{ resource['factor'] * 100 }}%</div>
			</div>
			<div>
				<div>Производство:</div>
				<div>{{ $formatNumber(resource['production']) }}</div>
			</div>
			<div>
				<div>Вместимость:</div>
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