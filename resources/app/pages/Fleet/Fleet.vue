<template>
	<Head :title="$t('menu.fleet')"/>
	<div class="fleet-page page-fleet">
		<div class="block">
			<div class="title fleet-heading">
				<span>{{ $t('pages.fleets.main.fleets') }} <span class="fleet-badge" :class="{ negative: page.fleets.length >= user.fleets_max }">{{ page.fleets.length }} / {{ user.fleets_max }}</span></span>
				<span v-if="page.maxExpeditions > 0" class="fleet-meta">
					{{ $t('pages.fleets.main.expeditions') }} {{ page.curExpeditions }} / {{ page.maxExpeditions }}
				</span>
			</div>
			<div class="content">
				<FleetList :fleets="page.fleets"/>
			</div>
		</div>
		<template v-if="!isVacation">
			<div v-if="page.ships.length" class="block">
				<div class="title fleet-heading">
					<span>{{ $t('pages.fleets.main.select_ships') }}</span>
					<Link href="/fleet/shortcut" class="fleet-secondary-link">{{ $t('pages.fleets.shortcut.index.title') }}</Link>
				</div>
				<div v-if="page.selected.mission > 0 || page.selected.galaxy > 0" class="fleet-selection-context">
					<span v-if="page.selected.mission > 0">{{ $t('fleet_mission.' + page.selected.mission) }}</span>
					<span v-if="page.selected.galaxy > 0">
						[{{ page.selected.galaxy }}:{{ page.selected.system }}:{{ page.selected.planet }}]
					</span>
				</div>
				<form method="post" @submit.prevent="checkout">
					<div class="fleet-ship-row fleet-table-heading">
						<span>{{ $t('pages.fleets.main.ship_type') }}</span>
						<span>{{ $t('pages.fleets.main.available') }}</span>
						<span>{{ $t('pages.fleets.main.quantity') }}</span>
					</div>
					<div v-for="ship in page.ships" :key="ship.id" class="fleet-ship-row" :class="{ 'is-selected': fleets[ship.id] > 0 }">
						<div class="fleet-ship-name">
							<img :src="'/assets/images/elements/' + ship.id + '.webp'" alt="" width="40" height="40">
							<span>{{ $t('tech.' + ship.id) }}</span>
						</div>
						<button
							v-if="ship.id !== 212"
							type="button"
							class="button fleet-available"
							:class="{ 'is-success': fleets[ship.id] === ship.count }"
							:aria-pressed="fleets[ship.id] === ship.count"
							:title="$t(fleets[ship.id] === ship.count ? 'pages.fleets.main.clear' : 'pages.fleets.main.select_all') + ': ' + $t('tech.' + ship.id)"
							@click="maxShips(ship.id)"
						>
							<span>{{ $formatNumber(ship.count) }}</span>
							<svg
								viewBox="0 0 16 16"
								fill="none"
								stroke="currentColor"
								stroke-width="1.5"
								stroke-linecap="round"
								stroke-linejoin="round"
								aria-hidden="true"
							>
								<path d="M3 8h10M9 4l4 4-4 4"/>
							</svg>
						</button>
						<span v-else class="fleet-available">{{ $formatNumber(ship.count) }}</span>
						<div v-if="ship.id !== 212" class="fleet-quantity">
							<button
								type="button"
								class="button is-secondary icon-button"
								@click="diffShips(ship.id, -1)"
								:aria-label="$t('pages.fleets.main.quantity_m')"
							>
								−
							</button>
							<input
								type="number"
								min="0"
								:max="ship.count"
								v-model.number="fleets[ship.id]"
								:aria-label="$t('tech.' + ship.id)"
								placeholder="0"
								@change.prevent="calculateShips"
								@keyup="calculateShips"
							>
							<button
								type="button"
								class="button is-secondary icon-button"
								@click="diffShips(ship.id, 1)"
								:aria-label="$t('pages.fleets.main.quantity_p')"
							>
								+
							</button>
						</div>
						<span v-else class="fleet-meta">—</span>
					</div>
					<div class="fleet-actions">
						<button type="button" class="button" @click="allShips">{{ $t('pages.fleets.main.select_all') }}</button>
						<button v-if="count" type="button" class="button is-secondary" @click="clearShips">
							{{ $t('pages.fleets.main.clear') }}
						</button>
					</div>
					<div v-if="count" class="fleet-selection-summary">
						<div>
							<span>{{ $t('pages.fleets.main.quantity') }}</span>
							<strong>{{ $formatNumber(count) }}</strong>
						</div>
						<div>
							<span>{{ $t('pages.fleets.main.capacity') }}</span>
							<strong>{{ allCapacity ? $formatNumber(allCapacity) : '—' }}</strong>
						</div>
						<div>
							<span>{{ $t('pages.fleets.main.speed') }}</span>
							<strong>{{ allSpeed ? $formatNumber(allSpeed) : '—' }}</strong>
						</div>
						<button v-if="page.fleets.length < user.fleets_max" type="submit" class="button">
							{{ $t('pages.fleets.main.next') }}
							<span aria-hidden="true">→</span>
						</button>
					</div>
				</form>
			</div>
			<div v-else class="block">
				<div class="title">{{ $t('pages.fleets.main.no_ships') }}</div>
				<div class="fleet-empty">
					<Link href="/shipyard" class="button">{{ $t('pages.fleets.main.go_to_shipyard') }}</Link>
				</div>
			</div>
		</template>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import FleetList from '~/components/Page/Fleet/FleetList.vue';
	import { computed, ref, watch } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { startLoading, stopLoading } from '~/composables/useLoading.js';

	const props = defineProps({
		page: Object,
	});

	const fleets = ref({});
	const allCapacity = ref(0);
	const allSpeed = ref(0);

	const state = useState();
	const user = computed(() => state.user);
	const isVacation = computed(() => user.value?.vacation !== null);

	const count = computed(() => {
		return props.page['ships'].reduce((total, ship) => {
			let cnt = fleets.value[ship.id] || 0;
			return (total + cnt);
		}, 0);
	});

	watch(() => props.page.ships, () => {
		init();
	});

	watch(fleets, () => {
		calculateShips();
	}, { deep: true });

	function init () {
		if (!props.page.ships) {
			return;
		}

		fleets.value = {};
	}

	function maxShips (shipId) {
		let ship = props.page['ships'].find((item) => {
			return item.id === shipId
		})

		if (typeof fleets.value[ship['id']] !== "undefined" && fleets.value[ship['id']] === ship['count']) {
			fleets.value[ship['id']] = '';
		} else {
			fleets.value[ship['id']] = ship['count'];
		}
	}

	function clearShips () {
		props.page.ships.forEach((ship) => {
			fleets.value[ship['id']] = '';
		})
	}

	function allShips () {
		props.page.ships.forEach((ship) => {
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

		let ship = props.page['ships'].find((item) => {
			return item.id === shipId
		})

		if (fleets.value[shipId] > ship.count)
			fleets.value[shipId] = ship.count;
	}

	function calculateShips () {
		let maxSpeed = 1000000000;
		let capacity = 0;
		let speed = maxSpeed;

		props.page['ships'].forEach((ship) => {
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
			...props.page['selected'],
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