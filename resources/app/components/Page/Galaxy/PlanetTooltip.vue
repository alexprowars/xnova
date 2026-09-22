<template>
	<div class="galaxy-tooltip-card">
		<div class="galaxy-tooltip-summary">
			<img class="galaxy-tooltip-image" :src="'/assets/images/planeten/small/s_' + item['planet']['image'] + '.jpg'" height="64" width="64" alt="">
			<div class="galaxy-tooltip-details">
				<span class="galaxy-tooltip-type">{{ $t('planet_type.' + item['planet']['type']) }}</span>
				<strong class="galaxy-tooltip-title">{{ item['planet']['name'] }}</strong>
				<span class="galaxy-tooltip-meta">[{{ item.position.galaxy }}:{{ item.position.system }}:{{ item.position.planet }}]</span>
			</div>
		</div>
		<div v-if="!isVacation && item.user" class="galaxy-tooltip-actions">
			<Link v-if="canPhalanx" class="button is-secondary" :href="'/phalanx?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet" target="_blank">
				<GalaxyIcon type="spy"/>
				{{ $t('pages.galaxy.phalanx') }}
			</Link>
			<template v-if="!isOwn">
				<Link as="button" type="button" class="button is-danger" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=' + item['planet']['type'] + '&mission=1'">
					<GalaxyIcon type="attack"/>
					{{ $t('fleet_mission.1') }}
				</Link>
				<Link as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=' + item['planet']['type'] + '&mission=5'">
					<GalaxyIcon type="hold"/>
					{{ $t('fleet_mission.5') }}
				</Link>
			</template>
			<Link v-else as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=' + item['planet']['type'] + '&mission=4'">
				<GalaxyIcon type="deploy"/>
				{{ $t('fleet_mission.4') }}
			</Link>
			<Link as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=' + item['planet']['type'] + '&mission=3'">
				<GalaxyIcon type="transport"/>
				{{ $t('fleet_mission.3') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import GalaxyIcon from './GalaxyIcon.vue';

	defineProps({
		item: Object,
		isVacation: Boolean,
		isOwn: Boolean,
		canPhalanx: Boolean,
	});
</script>