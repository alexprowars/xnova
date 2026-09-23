<template>
	<div v-if="planet" class="resource-panel">
		<PanelResource type="metal" :resource="planet.resources.metal"/>
		<PanelResource type="crystal" :resource="planet.resources.crystal"/>
		<PanelResource type="deuterium" :resource="planet.resources.deuterium"/>
		<PlanetPanelEnergy :resource="planet.resources.energy"/>
		<div class="resource-panel-item resource-credits">
			<Popper popper-class="officiers-tooltip">
				<Link href="/officiers" class="resource-panel-item-icon" :aria-label="$t('credits')">
					<ResourceIcon code="credits" aria-hidden="true" focusable="false"/>
				</Link>
				<template #content>
					<div class="resource-panel-officiers">
						<div class="resource-panel-officiers-title">{{ $t('menu.officiers') }}</div>
						<div class="resource-panel-officiers-list">
							<div v-for="officier in user.officiers" :key="officier.code" class="resource-panel-officier" :class="{ 'is-active': officier.date }">
								<span class="officier" :class="officier.code + (officier.date ? '_active' : '')" aria-hidden="true"></span>
								<div class="resource-panel-officier-info">
									<div class="resource-panel-officier-name">{{ $t('officiers.' + officier.code) }}</div>
									<div v-if="officier.date" class="resource-panel-officier-status" :title="$t('pages.overview.officier_active_until')">
										{{ $t('pages.overview.officier_active_until') }} {{ $formatDate(officier.date, 'DD MMM HH:mm') }}
									</div>
									<div v-else class="resource-panel-officier-status">{{ $t('pages.overview.officier_noactive') }}</div>
								</div>
							</div>
						</div>
					</div>
				</template>
			</Popper>
			<div class="resource-panel-item-info">
				<div class="resource-panel-item-label">{{ $t('credits') }}</div>
				<div class="resource-panel-item-value">{{ $formatNumber(user.credits) }}</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import PanelResource from './PlanetPanelResource.vue';
	import PlanetPanelEnergy from './PlanetPanelEnergy.vue';
	import { computed } from 'vue';
	import { Link } from '@inertiajs/vue3';
	import Popper from '~/components/Popper.vue';
	import { useIntervalFn } from '@vueuse/shared';

	let updated = Date.now();

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	if (planet.value) {
		useIntervalFn(() => {
			update();
		});
	}

	function update () {
		const now = Date.now();
		const factor = (now - updated) / 1000;

		if (factor < 0) {
			return;
		}

		updated = now;

		['metal', 'crystal', 'deuterium']
			.filter(res => typeof planet.value['resources'][res] !== 'undefined')
			.forEach(res => {
				let power = (planet.value['resources'][res]['value'] >= planet.value['resources'][res]['capacity']) ? 0 : 1;

				planet.value['resources'][res]['value'] += ((planet.value['resources'][res]['production'] / 3600) * power * factor);
			});
	}
</script>