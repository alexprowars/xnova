<template>
	<Head :title="$t('pages.phalanx.page_title')"/>
	<div class="game-page page-phalanx">
		<UiHeading class="game-heading">
			<h1>
				<RadarIcon aria-hidden="true"/>
				{{ $t('pages.phalanx.page_title') }}
			</h1>
			<UiCount>{{ page.items.length }}</UiCount>
		</UiHeading>
		<UiPanel :title="$t('pages.phalanx.activity_header')" class="game-panel">
			<UiEmptyState v-if="!page.items.length">{{ $t('pages.phalanx.no_movement') }}</UiEmptyState>
			<article
				v-for="(item, index) in page.items"
				:key="index"
				class="phalanx-flight"
				:class="{ 'is-returning': item.direction !== 1, 'is-espionage': item.mission === 6 }"
			>
				<div class="phalanx-time">
					<strong>{{ $formatTime(Math.max(0, dayjs(item.time).diff(now) / 1000), ':', true) }}</strong>
					<time>{{ $formatDate(item.time, 'HH:mm:ss') }}</time>
				</div>
				<div class="phalanx-route">
					<i18n-t keypath="pages.phalanx.fleet_row" tag="span" scope="global">
						<template #fleet>
							<Popper>
								<template #content>
									<div class="phalanx-fleet-composition">
										<div v-if="!Object.keys(item.fleet).length">{{ $t('fleet_activity.unknown') }}</div>
										<div v-for="(count, unit) in item.fleet" :key="unit" class="phalanx-fleet-unit">
											<span>{{ unit === 'total' ? $t('fleet_activity.count') : $t('tech.' + unit) }}</span>
											<span v-if="count !== null">{{ $formatNumber(count) }}</span>
										</div>
									</div>
								</template>
								<button type="button" class="phalanx-fleet-trigger">{{ $t('fleet_activity.fleet') }}</button>
							</Popper>
						</template>
						<template #type1>{{ $t('pages.phalanx.origin_' + item['type_1']) }}</template>
						<template #planetName>{{ item['planet_name'] }}</template>
						<template #pos1>
							{{ ' ' }}<PlanetLink
								:galaxy="item.planet_position.galaxy"
								:system="item.planet_position.system"
								:planet="item.planet_position.planet"
							/>
						</template>
						<template #direction>
							{{ item['direction'] === 1 ? $t('pages.phalanx.dir_outbound') : $t('pages.phalanx.dir_inbound') }}
						</template>
						<template #type2>{{ $t('pages.phalanx.target_' + item['type_2']) }}</template>
						<template #targetName>{{ item['target_name'] }}</template>
						<template #pos2>
							{{ ' ' }}<PlanetLink
								:galaxy="item.target_position.galaxy"
								:system="item.target_position.system"
								:planet="item.target_position.planet"
							/>
						</template>
						<template #mission>
							<span class="phalanx-coordinates">{{ $t('fleet_mission.' + item['mission']) }}</span>
						</template>
					</i18n-t>
				</div>
			</article>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiCount, UiEmptyState, UiHeading, UiPanel } from '~/components/UI';
	import PlanetLink from '~/components/PlanetLink.vue';
	import Popper from '~/components/Popper.vue';
	import RadarIcon from '~/images/icons/radar.svg?component';
	import { useUpdateInterval } from '~/composables/useUpdateInterval.js';
	import dayjs from 'dayjs';
	import { Head } from '@inertiajs/vue3';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		page: Object,
	});

	const now = useUpdateInterval();
</script>
