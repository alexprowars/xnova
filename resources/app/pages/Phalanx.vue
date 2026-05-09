<template>
	<Head :title="$t('pages.phalanx.page_title')"/>
	<table class="table">
		<tbody>
		<tr>
			<td class="c" colspan="2">
				{{ $t('pages.phalanx.activity_header') }}
			</td>
		</tr>
		<tr v-if="items.length === 0">
			<td class="th" colspan="2">{{ $t('pages.phalanx.no_movement') }}</td>
		</tr>
		<tr v-for="item in items">
			<td class="th">
				<div class="z">{{ $formatTime(dayjs(item['time']).diff(now) / 1000, ':', true) }}</div>
				<span :style="{ color: item['direction'] === 1 ? 'lime' : 'green' }">{{ $formatDate(item['time'], 'HH:mm:ss') }}</span>
			</td>
			<td class="th">
				<span :style="{ color: item['mission'] !== 6 ? 'lime' : 'orange' }">
					<i18n-t
						keypath="pages.phalanx.fleet_row"
						tag="span"
						scope="global"
						:values="{
							type1: item['type_1'],
							planetName: item['planet_name'],
							direction: item['direction'] === 1 ? $t('pages.phalanx.dir_outbound') : $t('pages.phalanx.dir_inbound'),
							type2: item['type_2'],
							targetName: item['target_name'],
						}"
					>
						<template #fleet>
							<span v-html="item['fleet']"></span>
						</template>
						<template #pos1>
							<span style="color: white"> [<span v-html="item['planet_position']"></span>]</span>
						</template>
						<template #pos2>
							<span style="color: white"> [<span v-html="item['target_position']"></span>]</span>
						</template>
						<template #mission>
							<span style="color: white">{{ $t('fleet_mission.' + item['mission']) }}</span>
						</template>
					</i18n-t>
				</span>
			</td>
		</tr>
		</tbody>
	</table>
</template>

<script setup>
	import { useNow } from '@vueuse/core';
	import dayjs from 'dayjs';
	import { Head } from '@inertiajs/vue3';
	import App from '~/App.vue';
	import EmptyLayout from '~/layouts/EmptyLayout.vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})

	const now = useNow({ interval: 1000 });
</script>