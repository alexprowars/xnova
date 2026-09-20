<template>
	<Head :title="$t('pages.phalanx.page_title')"/>
	<div class="game-page page-phalanx"><UiHeading class="game-heading"><h1><RadarIcon aria-hidden="true"/>{{ $t('pages.phalanx.page_title') }}</h1><UiCount class="game-count">{{ page.items.length }}</UiCount></UiHeading>
		<UiPanel :title="$t('pages.phalanx.activity_header')" class="game-panel"><UiEmptyState v-if="!page.items.length" class="game-empty">{{ $t('pages.phalanx.no_movement') }}</UiEmptyState>
			<article v-for="(item, index) in page.items" :key="index" class="phalanx-flight" :class="{ 'is-returning': item.direction !== 1, 'is-espionage': item.mission === 6 }"><div class="phalanx-time"><strong>{{ $formatTime(Math.max(0, dayjs(item.time).diff(now) / 1000), ':', true) }}</strong><time>{{ $formatDate(item.time, 'HH:mm:ss') }}</time></div><div class="phalanx-route">
					<i18n-t keypath="pages.phalanx.fleet_row" tag="span" scope="global">
						<template #fleet>
							<span v-html="item['fleet']"></span>
						</template>
						<template #type1>{{ item['type_1'] }}</template>
						<template #planetName>{{ item['planet_name'] }}</template>
						<template #pos1>
							<span class="phalanx-coordinates"> [<span v-html="item['planet_position']"></span>]</span>
						</template>
						<template #direction>{{ item['direction'] === 1 ? $t('pages.phalanx.dir_outbound') : $t('pages.phalanx.dir_inbound') }}</template>
						<template #type2>{{ item['type_2'] }}</template>
						<template #targetName>{{ item['target_name'] }}</template>
						<template #pos2>
							<span class="phalanx-coordinates"> [<span v-html="item['target_position']"></span>]</span>
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
	import RadarIcon from '~/images/icons/radar.svg?component';
	import { useNow } from '@vueuse/core';
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

	const now = useNow({ interval: 1000 });
</script>