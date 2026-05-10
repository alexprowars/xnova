<template>
	<div class="block-table text-center">
		<div class="grid">
			<div class="c"><span class="success">{{ $t('pages.fleets.send.sended') }}</span></div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.mission') }}</div>
			<div class="th">{{ $t('fleet_mission.' + page['mission']) }}</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.distance') }}</div>
			<div class="th">{{ $formatNumber(page['distance']) }}</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.speed') }}</div>
			<div class="th">{{ $formatNumber(page['speed']) }}</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.consumption') }}</div>
			<div class="th">{{ $formatNumber(page['consumption']) }}</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.deployment') }}</div>
			<div class="th">
				<Link :href="'/galaxy?galaxy=' + page['from']['galaxy'] + '&system=' + page['from']['system']">
					{{ page['from']['galaxy'] }}:{{ page['from']['system'] }}:{{ page['from']['planet'] }}
				</Link>
			</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.target') }}</div>
			<div class="th">
				<Link :href="'/galaxy?galaxy=' + page['target']['galaxy'] + '&system=' + page['target']['system']">
					{{ page['target']['galaxy'] }}:{{ page['target']['system'] }}:{{ page['target']['planet'] }}
				</Link>
			</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.arrival_target') }}</div>
			<div class="th">{{ $formatDate(page['start_time'], 'DD MMM HH:mm:ss') }}</div>
		</div>
		<div class="grid grid-cols-2">
			<div class="th">{{ $t('pages.fleets.send.arrival_home') }}</div>
			<div class="th">{{ $formatDate(page['end_time'], 'DD MMM HH:mm:ss') }}</div>
		</div>
		<div v-if="['units'].length" class="grid">
			<div class="c">{{ $t('pages.fleets.send.ships') }}</div>
		</div>
		<div v-for="(count, unit) in page['units']" class="grid grid-cols-2">
			<div class="th">{{ $t('tech.' + unit) }}</div>
			<div class="th">{{ $formatNumber(count) }}</div>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';

	defineProps({
		page: Object,
	});
</script>