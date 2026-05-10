<template>
	<Head title="Флот"/>
	<div class="page-fleet">
		<div class="page-fleet-fly block">
			<div class="title">
				<div class="grid grid-cols-2">
					<div class="text-left">
						{{ $t('pages.fleets.main.fleets') }}
						<span :class="[data.fleets.length < user['fleets_max'] ? 'positive' : 'negative']">{{ data.fleets.length }}</span>
						{{ $t('pages.fleets.main.of') }}
						<span class="negative">{{ user['fleets_max'] }}</span>
					</div>
					<div v-if="data['maxExpeditions'] > 0" class="text-right">
						{{ $t('pages.fleets.main.expeditions') }} {{ data['curExpeditions'] }}/{{ data['maxExpeditions'] }}
					</div>
				</div>
			</div>
			<div class="content">
				<FleetList :fleets="data.fleets"/>
			</div>
		</div>
		<template v-if="!isVacation">
			<div v-if="data.ships.length" class="block page-fleet-select">
				<div class="title">
					<div class="grid">
						{{ $t('pages.fleets.main.select_ships') }}<template v-if="data['selected']['mission'] > 0"> {{ $t('pages.fleets.main.select_mission') }} "{{ $t('fleet_mission.' + data['selected']['mission']) }}"</template><template v-if="data['selected']['galaxy'] > 0"> на координаты [{{ data['selected']['galaxy'] }}:{{ data['selected']['system'] }}:{{ data['selected']['planet'] }}]</template>
					</div>
				</div>
				<div class="content">
					<form method="post" class="block-table text-center fleet_ships" @submit.prevent="checkout">
						<div class="grid grid-cols-12 divide-x">
							<div class="col-span-6 sm:col-span-7 th">{{ $t('pages.fleets.main.ship_type') }}</div>
							<div class="col-span-2 sm:col-span-2 th">{{ $t('pages.fleets.main.quantity') }}</div>
							<div class="col-span-4 sm:col-span-3 th">&nbsp;</div>
						</div>
						<div v-for="ship in data.ships" class="grid grid-cols-12 divide-x">
							<div class="col-span-6 sm:col-span-7 th middle">
								<a :title="$t('tech.' + ship.id)">{{ $t('tech.' + ship.id) }}</a>
							</div>
							<div class="col-span-2 sm:col-span-2 th middle">
								<a @click.prevent="maxShips(ship['id'])">{{ $formatNumber(ship['count']) }}</a>
							</div>
							<div v-if="ship.id === 212" class="col-span-4 sm:col-span-3 th"></div>
							<div v-else class="col-span-4 sm:col-span-3 th">
								<a @click.prevent="diffShips(ship['id'], -1)" :title="$t('pages.fleets.main.quantity_m')" style="color:#FFD0D0">- </a>
								<input type="number" min="0" :max="ship['count']" v-model.number="fleets[ship['id']]" style="width:60%" :title="$t('tech.' + ship.id) + ': ' + ship['count']" placeholder="0" @change.prevent="calculateShips" @keyup="calculateShips">
								<a @click.prevent="diffShips(ship['id'], 1)" :title="$t('pages.fleets.main.quantity_p')" style="color:#D0FFD0"> +</a>
							</div>
						</div>
						<div class="grid grid-cols-12 divide-x">
							<div class="col-span-12 sm:col-span-7 th"></div>
							<div class="col-span-12 sm:col-span-5 th">
								<a class="button" @click.prevent="allShips">{{ $t('pages.fleets.main.select_all') }}</a>
								<a v-if="count" class="button" @click.prevent="clearShips">{{ $t('pages.fleets.main.clear') }}</a>
							</div>
						</div>
						<div v-if="count" class="grid grid-cols-12 divide-x">
							<div class="col-span-4 sm:col-span-7 th">&nbsp;</div>
							<div class="col-span-4 sm:col-span-2 th">{{ $t('pages.fleets.main.capacity') }}</div>
							<div class="col-span-4 sm:col-span-3 th">{{ allCapacity ? $formatNumber(allCapacity) : '-' }}</div>
						</div>
						<div v-if="count" class="grid grid-cols-12 divide-x">
							<div class="col-span-4 sm:col-span-7 th">&nbsp;</div>
							<div class="col-span-4 sm:col-span-2 th">{{ $t('pages.fleets.main.speed') }}</div>
							<div class="col-span-4 sm:col-span-3 th">{{ allSpeed ? $formatNumber(allSpeed) : '-'}}</div>
						</div>
						<div v-if="count && data.fleets.length < user['fleets_max']" class="grid">
							<div class="th"><button type="submit" class="button">{{ $t('pages.fleets.main.next') }}</button></div>
						</div>
					</form>
				</div>
			</div>
			<div v-else class="block">
				<div class="title text-center">{{ $t('pages.fleets.main.no_ships') }}</div>
				<div class="block-table text-center">
					<div class="grid">
						<div class="th">
							<Link href="/shipyard" class="button">{{ $t('pages.fleets.main.go_to_shipyard') }}</Link>
						</div>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>

<script setup>
	import FleetList from '~/components/Page/Fleet/FleetList.vue';
	import { computed, ref, watch } from 'vue';
	import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
	import { startLoading, stopLoading } from '~/composables/useLoading.js';

	const props = defineProps({
		data: {
			type: Object,
		}
	});

	const fleets = ref({});
	const allCapacity = ref(0);
	const allSpeed = ref(0);

	const page = usePage();
	const user = computed(() => page.props.user);
	const isVacation = computed(() => user.value?.vacation !== null);

	const count = computed(() => {
		return props.data['ships'].reduce((total, ship) => {
			let cnt = fleets.value[ship.id] || 0;
			return (total + cnt);
		}, 0);
	});

	watch(() => props.data.ships, () => {
		init();
	});

	watch(fleets, () => {
		calculateShips();
	}, { deep: true });

	function init () {
		if (!props.data.ships) {
			return;
		}

		fleets.value = {};
	}

	function maxShips (shipId) {
		let ship = props.data['ships'].find((item) => {
			return item.id === shipId
		})

		if (typeof fleets.value[ship['id']] !== "undefined" && fleets.value[ship['id']] === ship['count']) {
			fleets.value[ship['id']] = '';
		} else {
			fleets.value[ship['id']] = ship['count'];
		}
	}

	function clearShips () {
		props.data.ships.forEach((ship) => {
			fleets.value[ship['id']] = '';
		})
	}

	function allShips () {
		props.data.ships.forEach((ship) => {
			if (ship['id'] !== 212) {
				fleets.value[ship['id']] = ship['count'];
			}
		})
	}

	function diffShips (shipId, val) {
		if (typeof fleets.value[shipId] === "undefined") {
			fleets.value[shipId] = 0;
		}

		if (!parseInt(fleets.value[shipId]))
			fleets.value[shipId] = 0;

		fleets.value[shipId] += val;

		if (fleets.value[shipId] <= 0)
			fleets.value[shipId] = '';

		let ship = props.data['ships'].find((item) => {
			return item.id === shipId
		})

		if (fleets.value[shipId] > ship.count)
			fleets.value[shipId] = ship.count;
	}

	function calculateShips () {
		let maxSpeed = 1000000000;
		let capacity = 0;
		let speed = maxSpeed;

		props.data['ships'].forEach((ship) => {
			let cnt = fleets.value[ship.id] || 0;
			cnt = parseInt(cnt);

			if (isNaN(cnt))
				return;

			capacity += cnt * ship['capacity'];

			if (cnt > 0 && ship['speed'] > 0 && ship['speed'] < speed)
				speed = ship['speed'];
		})

		if ((speed <= 0) || (speed >= maxSpeed))
			speed = 0;

		allSpeed.value = speed;
		allCapacity.value = capacity;
	}

	async function checkout() {
		useForm({
			ships: fleets.value,
			...props.data['selected'],
		})
		.post('/fleet/checkout', {
			onSuccess(result) {
				//router.push(result);
			},
			onStart() {
				startLoading();
			},
			onFinish() {
				stopLoading();
			}
		});
	}
</script>