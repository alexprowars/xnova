<template>
	<div class="overview-fleets-row" :class="[fleetStatus[item.status], item.owner ? 'own' : '', fleetStyle[item.mission]]">
		<div class="overview-fleet-time">
			<Timer :value="item.date" class="overview-fleet-countdown"/>
			<time :datetime="item.date">{{ $formatDate(item.date, 'DD MMM HH:mm:ss') }}</time>
		</div>
		<div class="overview-fleet-details">
			<div class="overview-fleet-description">
				<template v-if="item['owner']">Ваш</template>
				<template v-else>{{ item['assault'] ? 'Союзный ' : 'Чужой' }}</template>

				<Popper popper-class="overview-fleet-tooltip">
					<template #content>
						<div class="overview-fleet-tooltip-content">
							<div v-if="!Object.keys(item['units']).length" class="text-center">
								Нет информации
							</div>
							<template v-else>
								<div v-for="(count, unit) in item['units']" class="grid grid-cols-4">
									<div v-if="count === null" class="col-span-4 text-center">{{ $t('tech.' + unit) }}</div>
									<div v-if="count !== null" class="col-span-3">{{ $t('tech.' + unit) }}:</div>
									<div v-if="count !== null" class="text-right">{{ $formatNumber(count) }}</div>
								</div>
								<div v-if="item['total']" class="grid grid-cols-2">
									<div>Численность:</div>
									<div class="text-right">{{ $formatNumber(item['total']) }}</div>
								</div>
							</template>
						</div>
					</template>

					<template v-if="units.length && item['mission'] === 1">
						<Link :href="'/sim?units=' + units" class="overview-fleet-trigger">флот</Link>
					</template>
					<button v-else type="button" class="overview-fleet-trigger">флот</button>
				</Popper>

				<template v-if="!item['owner']">
					игрока
					<template v-if="item['user']">
						{{ item['user']['name'] }} <Link :href="'/messages/write/' + item['user']['id']" :title="$t('send_message')" :aria-label="$t('send_message')"><SendIcon class="inline-block size-4 align-middle" aria-hidden="true" focusable="false"/></Link>
					</template>
				</template>

				<template v-if="item['status'] === 0">
					отправленный
					{{ start }} <Link :href="'/galaxy?galaxy=' + item['start']['galaxy'] + '&system=' + item['start']['system']">[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]</Link>
					направляется к
					{{ target }} <Link :href="'/galaxy?galaxy=' + item['target']['galaxy'] + '&system=' + item['target']['system']">[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]</Link>
				</template>
				<template v-else-if="item['status'] === 1">
					отправленный
					{{ start }} <Link :href="'/galaxy?galaxy=' + item['start']['galaxy'] + '&system=' + item['start']['system']">[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]</Link>

					<template v-if="item['mission'] === 5">защищает</template>
					<template v-else>исследует</template>

					{{ target }} <Link :href="'/galaxy?galaxy=' + item['target']['galaxy'] + '&system=' + item['target']['system']">[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]</Link>
				</template>
				<template v-else>
					отправленный
					{{ target }} <Link :href="'/galaxy?galaxy=' + item['target']['galaxy'] + '&system=' + item['target']['system']">[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]</Link>
					{{ start }} <Link :href="'/galaxy?galaxy=' + item['start']['galaxy'] + '&system=' + item['start']['system']">[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]</Link>
				</template>.
			</div>
			<div class="overview-fleet-mission"><span class="overview-fleet-mission-label">Задание:</span>
				<template v-if="item['resources']['metal'] > 0 || item['resources']['crystal'] > 0 || item['resources']['deuterium'] > 0">
					<Popper popper-class="overview-fleet-tooltip">
						<template #content>
							<div class="overview-fleet-tooltip-content">
								<div class="grid grid-cols-2">
									<div>{{ $t('resources.metal') }}</div>
									<div class="text-right">{{ $formatNumber(item['resources']['metal']) }}</div>
								</div>
								<div class="grid grid-cols-2">
									<div>{{ $t('resources.crystal') }}</div>
									<div class="text-right">{{ $formatNumber(item['resources']['crystal']) }}</div>
								</div>
								<div class="grid grid-cols-2">
									<div>{{ $t('resources.deuterium') }}</div>
									<div class="text-right">{{ $formatNumber(item['resources']['deuterium']) }}</div>
								</div>
							</div>
						</template>
						<button type="button" class="overview-fleet-trigger">{{ $t('fleet_mission.' + item.mission) }}</button>
					</Popper>
				</template>
				<template v-else>
					{{ $t('fleet_mission.' + item.mission) }}
				</template>
			</div>
		</div>
	</div>
</template>

<script setup>
	import SendIcon from '~/images/icons/send.svg?component';
	import { computed } from 'vue';
	import { Link } from '@inertiajs/vue3';
	import Timer from '~/components/Timer.vue';
	import Popper from '~/components/Popper.vue';

	const { item } = defineProps({
		item: Object
	});

	const units = computed(() => {
		let result = '';

		for (let init in item['units']) {
			if (init === 'total' || item['units'][init] === null) {
				continue;
			}

			result += init + ',' + item['units'][init] + ';';
		}

		return result;
	});

	const start = computed(() => {
		let result = '';

		if (item['status'] !== 2) {
			if (item['start_name'] === null || item['start_name'] === '') {
				result = ' с координат ';
			} else {
				if (item['start']['planet_type'] === 1) {
					result = 'с планеты';
				} else if (item['start']['planet_type'] === 3) {
					result = 'с луны';
				} else if (item['start']['planet_type'] === 5) {
					result = 'с военной базы';
				}

				result += ' ' + item['start_name'] + ' ';
			}
		} else {
			if (item['start_name'] === null || item['start_name'] === '') {
				result = ' на координаты ';
			} else {
				if (item['start']['planet_type'] === 1) {
					result = 'возвращается на планету';
				} else if (item['start']['planet_type'] === 3) {
					result = 'возвращается на луну';
				} else if (item['start']['planet_type'] === 5) {
					result = 'возвращается на военную базу';
				}

				result += ' ' + item['start_name'] + ' ';
			}
		}

		return result;
	});

	const target = computed(() => {
		let result = '';

		if (item['status'] !== 2) {
			if (item['target_name'] === null || item['target_name'] === '') {
				result = ' координаты ';
			} else {
				if (item['mission'] !== 15 && item['mission'] !== 5) {
					if (item['target']['planet_type'] === 1) {
						result = 'планете';
					} else if (item['target']['planet_type'] === 3) {
						result = 'луне';
					} else if (item['target']['planet_type'] === 2) {
						result = 'полю обломков';
					} else if (item['target']['planet_type'] === 5) {
						result = ' военной базе ';
					}
				} else {
					result = 'координатам';
				}

				result += ' ' + item['target_name'] + ' ';
			}
		} else {
			if (item['target_name'] === null || item['target_name'] === '') {
				result = ' с координат ';
			} else {
				if (item['mission'] !== 15) {
					if (item['target']['planet_type'] === 1) {
						result = 'с планеты';
					} else if (item['target']['planet_type'] === 3) {
						result = 'с луны';
					} else if (item['target']['planet_type'] === 2) {
						result = 'с поля обломков';
					} else if (item['target']['planet_type'] === 5) {
						result = ' с военной базы ';
					}
				} else {
					result = 'с позиции';
				}

				result += ' ' + item['target_name'] + ' ';
			}
		}

		return result;
	});

	const fleetStyle = {
		1: 'attack',
		2: 'federation',
		3: 'transport',
		4: 'deploy',
		5: 'transport',
		6: 'espionage',
		7: 'colony',
		8: 'harvest',
		9: 'destroy',
		10: 'missile',
		15: 'transport',
		20: 'attack',
	};

	const fleetStatus = {
		0: 'flight',
		1: 'holding',
		2: 'return',
	};
</script>