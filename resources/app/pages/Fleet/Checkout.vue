<template>
	<div class="fleet-page page-fleet-checkout">
		<form ref="formRef" method="post" @submit.prevent="send">
			<div class="block">
				<div class="title">{{ $t('pages.fleets.checkout.sending') }}</div>
				<div class="fleet-route-form">
					<div class="fleet-field">
						<span>{{ $t('pages.fleets.checkout.target') }}</span>
						<div class="fleet-coordinates-input">
							<input
								type="number"
								min="1"
								:max="page.galaxy_max"
								v-model.number="target.galaxy"
								:aria-label="$t('pages.fleets.shortcut.form.title_galaxy')"
							>
							<span>:</span>
							<input
								type="number"
								min="1"
								:max="page.system_max"
								v-model.number="target.system"
								:aria-label="$t('pages.fleets.shortcut.form.title_system')"
							>
							<span>:</span>
							<input
								type="number"
								min="1"
								:max="page.planet_max"
								v-model.number="target.planet"
								:aria-label="$t('pages.fleets.shortcut.form.title_planet')"
							>
							<select
								name="planet_type"
								v-model.number="target.planet_type"
								:aria-label="$t('pages.fleets.checkout.target_type')"
							>
								<option v-for="index in Object.keys($tm('planet_type'))" :key="index" :value="index">
									{{ $t('planet_type.' + index) }}
								</option>
							</select>
						</div>
					</div>
					<label class="fleet-field">
						<span>{{ $t('pages.fleets.checkout.speed') }}</span>
						<select name="speed" v-model="speed" @change="info">
							<option v-for="i in 10" :key="i" :value="11 - i">{{ (11 - i) * 10 }}%</option>
						</select>
					</label>
				</div>
				<div class="fleet-flight-metrics">
					<div>
						<span>{{ $t('pages.fleets.checkout.distance') }}</span>
						<strong>{{ $formatNumber(distance) }}</strong>
					</div>
					<div>
						<span>{{ $t('pages.fleets.checkout.duration') }}</span>
						<strong>{{ $formatTime(duration, ':', true) }}</strong>
					</div>
					<div>
						<span>{{ $t('pages.fleets.checkout.arrival') }}</span>
						<strong>{{ $formatDate(target_time, 'DD MMM HH:mm:ss') }}</strong>
					</div>
					<div>
						<span>{{ $t('pages.fleets.checkout.max_speed') }}</span>
						<strong>{{ $formatNumber(maxspeed) }}</strong>
					</div>
					<div>
						<span>{{ $t('pages.fleets.checkout.consumption') }}</span>
						<strong :class="[storage > consumption ? 'positive' : 'negative']">{{ $formatNumber(consumption) }}</strong>
					</div>
					<div>
						<span>{{ $t('pages.fleets.checkout.capacity') }}</span>
						<strong :class="[storage > consumption ? 'positive' : 'negative']">{{ $formatNumber(storage) }}</strong>
					</div>
				</div>
			</div>
			<div class="block">
				<div class="title fleet-heading">
					<span>{{ $t('pages.fleets.checkout.links') }}</span>
					<Link href="/fleet/shortcut" class="fleet-secondary-link">{{ $t('pages.fleets.checkout.links_edit') }}</Link>
				</div>
				<div v-if="page.shortcuts.length" class="fleet-destination-list">
					<button
						v-for="link in page.shortcuts"
						:key="link.id"
						type="button"
						class="fleet-destination"
						@click="setTarget(link.galaxy, link.system, link.planet, link.planet_type)"
					>
						<strong>{{ link.name }}</strong>
						<span>[{{ link.galaxy }}:{{ link.system }}:{{ link.planet }}] · {{ $t('planet_type.' + link.planet_type) }}</span>
					</button>
				</div>
				<div v-else class="fleet-actions">
					<Link href="/fleet/shortcut/create" class="button">{{ $t('pages.fleets.checkout.links_add') }}</Link>
				</div>
				<template v-if="page.planets.length">
					<div class="fleet-section-title">{{ $t('pages.fleets.checkout.planets') }}</div>
					<div class="fleet-destination-list">
						<button
							v-for="planet in page.planets"
							:key="planet.id"
							type="button"
							class="fleet-destination"
							@click="setTarget(planet.galaxy, planet.system, planet.planet, planet.planet_type)"
						>
							<strong>{{ planet.name }}</strong>
							<span>[{{ planet.galaxy }}:{{ planet.system }}:{{ planet.planet }}]</span>
						</button>
					</div>
				</template>
				<template v-if="page.moons.length">
					<div class="fleet-section-title">
						{{ $t('pages.fleets.checkout.gates') }}
						<span v-if="page.gate_time" class="fleet-meta">
							{{ $t('pages.fleets.checkout.gates_charge', [$formatTime(dayjs(page.gate_time).diff(now) / 1000, ':', true)]) }}
						</span>
					</div>
					<div class="fleet-destination-list">
						<label
							v-for="item in page.moons"
							:key="item.id"
							class="fleet-destination fleet-gate"
							:class="{ 'is-selected': moon === item.id }"
						>
							<input type="radio" v-model="moon" :value="item.id">
							<span>
								<strong>{{ item.name }}</strong>
								<span>[{{ item.galaxy }}:{{ item.system }}:{{ item.planet }}]</span>
								<span v-if="item.jumpgate">{{ $formatTime(dayjs(item.jumpgate).diff(now) / 1000, ':', true) }}</span>
							</span>
						</label>
					</div>
				</template>
				<template v-if="page.alliances.length">
					<div class="fleet-section-title">{{ $t('pages.fleets.checkout.combat_alliances') }}</div>
					<div class="fleet-destination-list">
						<button
							v-for="(row, index) in page.alliances"
							:key="row.id"
							type="button"
							class="fleet-destination"
							:class="{ 'is-selected': alliance === row.id }"
							@click="allianceSet(index)"
						>
							{{ row.name }}
						</button>
					</div>
				</template>
			</div>
			<div class="fleet-checkout-columns">
				<div class="block">
					<div class="title">{{ $t('pages.fleets.checkout.mission') }}</div>
					<div class="fleet-missions">
						<label v-for="item in page.missions" :key="item" class="fleet-mission" :class="{ 'is-selected': mission === item }">
							<input :id="'m_' + item" type="radio" v-model="mission" :value="item">
							<span>{{ $t('fleet_mission.' + item) }}<small v-if="item === 15" class="negative">{{ $t('pages.fleets.checkout.expedition_warning') }}</small></span>
						</label>
						<div v-if="!page.missions.length" class="fleet-empty negative">
							{{ $t('pages.fleets.checkout.mission_impossible') }}
						</div>
					</div>
					<div class="fleet-arrival-note">
						{{ $t('pages.fleets.checkout.arrival_time', [$formatDate(target_time, 'DD MMM HH:mm:ss')]) }}
					</div>
				</div>
				<div class="block">
					<div class="title">{{ $t('pages.fleets.checkout.resources') }}</div>
					<div class="fleet-cargo">
						<div v-for="type in ['metal', 'crystal', 'deuterium']" :key="type" class="fleet-cargo-row">
							<label :for="'cargo-' + type">{{ $t('resources.' + type) }}</label>
							<button type="button" class="fleet-max" @click="maxRes(type)">
								{{ $t('pages.fleets.checkout.resources_max') }}
							</button>
							<input :id="'cargo-' + type" v-model.number="resource[type]" type="text" inputmode="numeric">
						</div>
					</div>
					<div class="fleet-cargo-remaining">
						<span>{{ $t('pages.fleets.checkout.resources_remaining') }}</span>
						<strong :class="[capacity >= 0 ? 'positive' : 'negative']">{{ $formatNumber(capacity) }}</strong>
					</div>
					<div class="fleet-actions">
						<button type="button" class="button" @click="maxResAll">{{ $t('pages.fleets.checkout.resources_all') }}</button>
						<button type="button" class="button is-secondary" @click="clearResAll">
							{{ $t('pages.fleets.checkout.resources_clear') }}
						</button>
					</div>
					<label v-if="mission === 15 && page.missions.includes(15)" class="fleet-field fleet-hold">
						<span>{{ $t('pages.fleets.checkout.expedition_time') }}</span>
						<select name="expeditiontime" v-model.number="expedition_hours">
							<option v-for="i in page.expedition_hours" :key="i" :value="i">
								{{ i }} {{ $t('pages.fleets.checkout.expedition_hour') }}
							</option>
						</select>
					</label>
					<div v-if="mission === 5 && page.missions.includes(5)" class="fleet-hold">
						<label class="fleet-field">
							<span>{{ $t('pages.fleets.checkout.orbit_hours') }}</span>
							<select name="holdingtime" v-model.number="hold_hours">
								<option v-for="hours in [0, 1, 2, 4, 8, 16, 32]" :key="hours" :value="hours">{{ hours }}</option>
							</select>
						</label>
						<div v-if="hold > 0" class="fleet-meta" v-html="$t('pages.fleets.checkout.orbit_requires', [$formatNumber(hold)])"></div>
					</div>
				</div>
			</div>
			<div class="fleet-actions fleet-submit">
				<Link href="/fleet" class="button is-secondary">{{ $t('pages.fleets.shortcut.index.back_fleet') }}</Link>
				<button v-if="page.missions.length" type="submit" class="button">
					{{ $t('pages.fleets.checkout.next') }}
					<span aria-hidden="true">→</span>
				</button>
			</div>
		</form>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed, onMounted, ref, watch } from 'vue';
	import dayjs from 'dayjs';
	import { useUpdateInterval } from '~/composables/useUpdateInterval.js';
	import { Link, useForm } from '@inertiajs/vue3';
	import { getConsumption, getDistance, getDuration, getSpeed, getStorage } from '~/utils/fleet.js';
	import { startLoading, stopLoading } from '~/composables/useLoading.js';

	const props = defineProps({
		page: Object,
	});

	const target = ref(props.page.target);

	const formRef = ref();
	const resource = ref({
		metal: 0, crystal: 0, deuterium: 0,
	});
	const speed = ref(10);
	const distance = ref(0);
	const duration = ref(0);
	const storage = ref(0);
	const maxspeed = ref(0);
	const consumption = ref(0);
	const mission = ref(props.page.mission);
	const moon = ref();

	const now = useUpdateInterval();
	const target_time = computed(() => now.value.getTime() + (duration.value * 1000));

	const alliance = ref(0);
	const expedition_hours = ref(1);
	const hold_hours = ref(1);

	const state = useState();
	const planet = computed(() => state.planet);

	const hold = computed(() => {
		let hold = 0;

		if (mission.value === 5) {
			hold = props.page['ships'].reduce((summ, item) => summ + item['stay'] * item['count'] * hold_hours.value, 0);
		}

		return hold;
	})

	const capacity = computed(() => {
		return storage.value - resource.value.metal - resource.value.crystal - resource.value.deuterium - hold.value;
	})

	onMounted(() => {
		info();
	});

	watch([target, alliance], () => {
		let ships = {}
		props.page['ships'].forEach((item) => ships[item['id']] = item['count']);

		useForm({
			...target.value, ships,
			alliance: alliance.value,
		})
		.post('/fleet/checkout', {
			preserveScroll: true,
			onSuccess() {
				info();
			},
			onStart() {
				startLoading();
			},
			onFinish() {
				stopLoading();
			}
		});
	}, { deep: true });

	function info () {
		distance.value = getDistance(planet.value['coordinates'], target.value);
		maxspeed.value = getSpeed(props.page['ships']);

		duration.value = getDuration({
			factor: speed.value,
			distance: distance.value,
			max_speed: maxspeed.value,
			universe_speed: state.speed['fleet']
		});

		consumption.value = getConsumption({
			ships: props.page['ships'],
			duration: duration.value,
			distance: distance.value,
			universe_speed: state.speed['fleet']
		});

		storage.value = getStorage(props.page['ships']) - consumption.value;

		if (!props.page.missions.includes(mission.value)) {
			mission.value = props.page.mission;
		}
	}

	function setTarget (galaxy, system, planet, type) {
		target.value['galaxy'] = galaxy
		target.value['system'] = system
		target.value['planet'] = planet

		if (typeof type === 'undefined')
			type = 1

		target.value['planet_type'] = type
	}

	function allianceSet (index) {
		let al = props.page['alliances'][index]

		alliance.value = al['id']
		setTarget(al['galaxy'], al['system'], al['planet'], al['planet_type'])
	}

	function maxRes (type) {
		let current = resource.value.metal + resource.value.crystal + resource.value.deuterium
		current -= resource.value[type]

		let free = storage.value - hold.value - current

		if (type === 'deuterium') {
			resource.value[type] = Math.max(Math.min(Math.floor(planet.value['resources'][type]['value'] - consumption.value - hold.value), free), 0)
		} else {
			resource.value[type] = Math.max(Math.min(Math.floor(planet.value['resources'][type]['value']), free), 0)
		}
	}

	function maxResAll () {
		clearResAll()
		maxRes('metal')
		maxRes('crystal')
		maxRes('deuterium')
	}

	function clearResAll () {
		resource.value.metal = resource.value.crystal = resource.value.deuterium = 0
	}

	function send() {
		let ships = {};
		props.page.ships.forEach((ship) => ships[ship.id] = ship.count);

		useForm({
			ships,
			...target.value,
			alliance: alliance.value,
			fleet: props.page['fleet'],
			mission: mission.value,
			expeditiontime: mission.value === 15 ? expedition_hours.value : 0,
			holdingtime: mission.value === 5 ? hold_hours.value : 0,
			moon: moon.value,
			speed: speed.value,
			resource: resource.value,
		})
		.post('/fleet/send');
	}
</script>