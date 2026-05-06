<template>
	<div v-if="resources" class="block">
		<div class="title">{{ $t('pages.info.combat.production_costs') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('resources.metal') }}</div>
					<div class="th">{{ $formatNumber(resources['metal']['base']) }} ({{ $formatNumber(resources['metal']['full']) }})</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('resources.crystal') }}</div>
					<div class="th">{{ $formatNumber(resources['crystal']['base']) }} ({{ $formatNumber(resources['crystal']['full']) }})</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('resources.deuterium') }}</div>
					<div class="th">{{ $formatNumber(resources['deuterium']['base']) }} ({{ $formatNumber(resources['deuterium']['full']) }})</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
	defineProps({
		resources: {
			type: Object,
		}
	});
</script>