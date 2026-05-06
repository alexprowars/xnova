<template>
	<div class="block">
		<div class="title">{{ $t('pages.info.combat.characteristics') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="item !== 212" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.info.combat.attack') }}</div>
					<div class="th">{{ $formatNumber(data['attack']) }} ({{ $formatNumber(data['attack_full']) }})</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.info.combat.armor') }}</div>
					<div class="th">{{ $formatNumber(data['armor']) }} ({{ $formatNumber(data['armor_full']) }})</div>
				</div>
				<template v-if="item !== 212">
					<div v-if="data['shield'] > 0" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.shield') }}</div>
						<div class="th">{{ $formatNumber(data['shield']) }}</div>
					</div>
					<div v-if="data['capacity'] > 0" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.capacity') }}</div>
						<div class="th">{{ $formatNumber(data['capacity']) }}</div>
					</div>
					<div v-if="data['speed'] > 0" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.speed') }}</div>
						<div class="th">{{ $formatNumber(data['speed']) }} ({{ $formatNumber(data['speed_full']) }})</div>
					</div>
					<div class="grid grid-cols-2">
						<div v-if="data['consumption'] > 0" class="th">{{ $t('pages.info.combat.fuel_consumption') }}</div>
						<div class="th">{{ $formatNumber(data['consumption']) }}</div>
					</div>
					<div v-if="data['type_engine']" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.engine_type') }}</div>
						<div class="th">{{ $t('engine_type.' + data['type_engine']) }}</div>
					</div>
					<div v-if="data['type_weapon']" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.weapon_type') }}</div>
						<div class="th">{{ $t('weapon_type.' + data['type_weapon']) }}</div>
					</div>
					<div v-if="data['type_armour']" class="grid grid-cols-2">
						<div class="th">{{ $t('pages.info.combat.armor_type') }}</div>
						<div class="th">{{ $t('armor_type.' + data['type_armour']) }}</div>
					</div>
				</template>
			</div>
		</div>
	</div>
	<RapidfireBlock :rapidfire="data['rapidfire']"/>
	<CostBlock :resources="data['resources']"/>
</template>

<script setup>
	import RapidfireBlock from './RapidfireBlock.vue';
	import CostBlock from './CostBlock.vue';

	defineProps({
		data: {
			type: Object,
		},
		item: {
			type: Number,
		}
	})
</script>