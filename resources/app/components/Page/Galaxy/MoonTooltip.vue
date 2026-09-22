<template>
	<div class="galaxy-tooltip-card galaxy-moon-card">
		<div class="galaxy-tooltip-summary">
			<img class="galaxy-tooltip-image" src="/assets/images/planeten/mond.jpg" height="64" width="64" alt="">
			<div class="galaxy-tooltip-details">
				<span class="galaxy-tooltip-type">{{ $t('planet_type.3') }}</span>
				<strong class="galaxy-tooltip-title">{{ item.moon.name }}</strong>
				<span class="galaxy-tooltip-meta">[{{ item.position.galaxy }}:{{ item.position.system }}:{{ item.position.planet }}]</span>
			</div>
		</div>
		<dl class="galaxy-tooltip-parameters">
			<div>
				<dt>{{ $t('pages.galaxy.moon_diameter') }}</dt>
				<dd>{{ $formatNumber(item.moon.diameter) }}</dd>
			</div>
			<div>
				<dt>{{ $t('pages.galaxy.moon_temp') }}</dt>
				<dd>{{ item.moon.temp }}</dd>
			</div>
		</dl>
		<div v-if="!isVacation && item.user" class="galaxy-tooltip-actions">
			<template v-if="!isOwn">
				<Link as="button" type="button" class="button is-danger" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=3&mission=1'">
					<GalaxyIcon type="attack"/>
					{{ $t('fleet_mission.1') }}
				</Link>
				<Link as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=3&mission=5'">
					<GalaxyIcon type="hold"/>
					{{ $t('fleet_mission.5') }}
				</Link>
				<Link v-if="canDestroy" as="button" type="button" class="button is-danger" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=3&mission=9'">
					<AlertIcon aria-hidden="true" focusable="false"/>
					{{ $t('fleet_mission.9') }}
				</Link>
			</template>
			<Link v-else as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=3&mission=4'">
				<GalaxyIcon type="deploy"/>
				{{ $t('fleet_mission.4') }}
			</Link>
			<Link as="button" type="button" class="button is-secondary" :href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=3&mission=3'">
				<GalaxyIcon type="transport"/>
				{{ $t('fleet_mission.3') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import GalaxyIcon from './GalaxyIcon.vue';
	import AlertIcon from '~/images/icons/alert.svg?component';

	defineProps({
		item: Object,
		isVacation: Boolean,
		isOwn: Boolean,
		canDestroy: Boolean,
	});
</script>