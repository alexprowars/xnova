<template>
	<div class="block">
		<div class="title">{{ $t('pages.fleets.checkout.sending') }}</div>
		<div class="content">
			<form ref="formRef" class="block-table text-center" method="post" @submit.prevent="send">
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.fleets.checkout.target') }}</div>
					<div class="th middle gap-2 fleet-coordinates-input">
						<input type="number" min="1" :max="data['galaxy_max']" v-model="data['target']['galaxy']">
						<input type="number" min="1" :max="data['system_max']" v-model="data['target']['system']">
						<input type="number" min="1" :max="data['planet_max']" v-model="data['target']['planet']">
						<select name="planet_type" v-model="data['target']['planet_type']">
							<option v-for="index in Object.keys($tm('planet_type'))" :value="index">{{ $t('planet_type.' + index) }}</option>
						</select>
					</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.fleets.checkout.speed') }}</div>
					<div class="th middle gap-2">
						<select name="speed" v-model="speed" @change="info">
							<option v-for="i in 10" :value="11 - i">{{ (11 - i) * 10 }}</option>
						</select> %
					</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.distance') }}</div>
					<div class="th">{{ $formatNumber(distance) }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.duration') }}</div>
					<div class="th">{{ $formatTime(duration, ':', true) }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.arrival') }}</div>
					<div class="th">{{ $formatDate(target_time, 'DD MMM HH:mm:ss') }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.max_speed') }}</div>
					<div class="th">{{ $formatNumber(maxspeed) }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.consumption') }}</div>
					<div class="th"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ $formatNumber(consumption) }}</span></div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.fleets.checkout.capacity') }}</div>
					<div class="th"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ $formatNumber(storage) }}</span></div>
				</div>
				<div class="grid">
					<div class="c">{{ $t('pages.fleets.checkout.links') }} <Link href="/fleet/shortcut">{{ $t('pages.fleets.checkout.links_edit') }}</Link></div>
				</div>

				<div v-if="data['shortcuts'].length > 0" class="grid" :class="{'grid-cols-2': data['shortcuts'].length !== 1}">
					<div v-for="link in data['shortcuts']" class="th">
						<a @click.prevent="setTarget(link['galaxy'], link['system'], link['planet'], link['planet_type'])">
							{{ link['name'] }} {{ link['galaxy'] }}:{{ link['system'] }}:{{ link['planet'] }}
							<span v-if="link['planet_type'] === 1">(P)</span>
							<span v-if="link['planet_type'] === 2">(D)</span>
							<span v-if="link['planet_type'] === 3">(L)</span>
						</a>
					</div>
				</div>
				<div v-else class="grid">
					<div class="th">
						<Link href="/fleet/shortcut/create" class="button">
							{{ $t('pages.fleets.checkout.links_add') }}
						</Link>
					</div>
				</div>

				<div v-if="data['planets'].length > 0" class="grid">
					<div class="c">{{ $t('pages.fleets.checkout.planets') }}</div>
				</div>
				<div v-if="data['planets'].length > 0" class="grid grid-cols-2">
					<div v-for="(planet, i) in data['planets']" class="th" :class="['col-span-'+(data['planets'].length % 2 > 0 && i === data['planets'].length - 1 ? 2 : 1)]">
						<a @click.prevent="setTarget(planet['galaxy'], planet['system'], planet['planet'], planet['planet_type'])">
							{{ planet['name'] }} {{ planet['galaxy'] }}:{{ planet['system'] }}:{{ planet['planet'] }}
						</a>
					</div>
				</div>

				<div v-if="data['moons'].length > 0" class="grid">
					<div class="c">
						{{ $t('pages.fleets.checkout.gates') }}
						<span v-if="data['gate_time']" class="small">{{ $t('pages.fleets.checkout.gates_charge', [$formatTime((dayjs(data['gate_time']).diff(now) / 1000), ':', true)]) }}</span>
					</div>
				</div>
				<div v-if="data['moons'].length > 0" class="grid grid-cols-2">
					<div v-for="(item, i) in data['moons']" class="th" :class="['col-span-'+(data['moons'].length % 2 > 0 && i === data['moons'].length - 1 ? 2 : 1)]">
						<input type="radio" v-model="moon" :value="item['id']" :id="'moon' + item['id']">
						<label :for="'moon'+item['id']">
							{{ item['name'] }} [{{ item['galaxy'] }}:{{ item['system'] }}:{{ item['planet'] }}]
							<span v-if="item['jumpgate']">{{ $formatTime((dayjs(data['jumpgate']).diff(now) / 1000), ':', true) }}</span>
						</label>
					</div>
				</div>

				<div v-if="data['alliances'].length > 0" class="grid">
					<div class="c">{{ $t('pages.fleets.checkout.combat_alliances') }}</div>
				</div>
				<div v-for="(row, index) in data['alliances']" class="grid">
					<div class="th">
						<a @click.prevent="allianceSet(index)">({{ row['name'] }})</a>
					</div>
				</div>

				<div class="grid grid-cols-2">
					<div class="th">
						<div class="block">
							<div class="title">{{ $t('pages.fleets.checkout.mission') }}</div>
							<div class="content">
								<div class="block-table">
									<div v-for="mission in data['missions']">
										<div class="th flex items-center gap-2" style="text-align: left !important">
											<input :id="'m_'+mission" type="radio" v-model="data['mission']" :value="mission">
											<label :for="'m_'+mission">{{ $t('fleet_mission.'+mission) }}</label>

											<span v-if="mission === 15" class="text-center negative">
												{{ $t('pages.fleets.checkout.expedition_warning') }}
											</span>
										</div>
									</div>
									<div v-if="data['missions'].length === 0">
										<div class="th negative">{{ $t('pages.fleets.checkout.mission_impossible') }}</div>
									</div>
									<div>
										<div class="th">{{ $t('pages.fleets.checkout.arrival_time', [$formatDate(target_time, 'DD MMM HH:mm:ss')]) }}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="th">
						<div class="block">
							<div class="title">{{ $t('pages.fleets.checkout.resources') }}</div>
							<div class="content">
								<div class="block-table">
									<div class="grid grid-cols-5">
										<div class="th col-span-2 middle">{{ $t('resources.metal') }}</div>
										<div class="th middle"><a @click.prevent="maxRes('metal')">{{ $t('pages.fleets.checkout.resources_max') }}</a></div>
										<div class="th col-span-2 middle"><input v-model="resource.metal" :alt="$t('resources.metal')" size="10" type="text"></div>
									</div>
									<div class="grid grid-cols-5">
										<div class="th col-span-2 middle">{{ $t('resources.crystal') }}</div>
										<div class="th middle"><a @click.prevent="maxRes('crystal')">{{ $t('pages.fleets.checkout.resources_max') }}</a></div>
										<div class="th col-span-2 middle"><input v-model="resource.crystal" :alt="$t('resources.crystal')" size="10" type="text"></div>
									</div>
									<div class="grid grid-cols-5">
										<div class="th col-span-2 middle">{{ $t('resources.deuterium') }}</div>
										<div class="th middle"><a @click.prevent="maxRes('deuterium')">{{ $t('pages.fleets.checkout.resources_max') }}</a></div>
										<div class="th col-span-2 middle"><input v-model="resource.deuterium" :alt="$t('resources.deuterium')" size="10" type="text"></div>
									</div>
									<div class="grid grid-cols-5">
										<div class="th col-span-2">{{ $t('pages.fleets.checkout.resources_remaining') }}</div>
										<div class="th col-span-3">
											<span :class="[capacity >= 0 ? 'positive' : 'negative']">{{ $formatNumber(capacity) }}</span>
										</div>
									</div>
									<div>
										<div class="th"><a @click.prevent="maxResAll">{{ $t('pages.fleets.checkout.resources_all') }}</a> | <a @click.prevent="clearResAll">{{ $t('pages.fleets.checkout.resources_clear') }}</a></div>
									</div>
									<div>
										<div class="th">&nbsp;</div>
									</div>

									<div v-if="data['mission'] === 15 && data['missions'].indexOf(15) >= 0" class="mission m_15">
										<div class="c">{{ $t('pages.fleets.checkout.expedition_time') }}</div>
									</div>
									<div v-if="data['mission'] === 15 && data['missions'].indexOf(15) >= 0" class="mission m_15">
										<div class="th">
											<select name="expeditiontime">
												<option v-for="i in data['expedition_hours']" :value="i">{{ i }} {{ $t('pages.fleets.checkout.expedition_hour') }}</option>
											</select>
										</div>
									</div>

									<div v-if="data['mission'] === 5 && data['missions'].indexOf(5) >= 0" class="mission m_5">
										<div class="c">{{ $t('pages.fleets.checkout.orbit_hours') }}</div>
									</div>
									<div v-if="data['mission'] === 5 && data['missions'].indexOf(5) >= 0" class="mission m_5">
										<div class="th">
											<select name="holdingtime" v-model="hold_hours">
												<option value="0">0</option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="4">4</option>
												<option value="8">8</option>
												<option value="16">16</option>
												<option value="32">32</option>
											</select>
											<div v-if="hold > 0" class="mt-2" v-html="$t('pages.fleets.checkout.orbit_requires', [$formatNumber(hold)])"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div v-if="data['missions'].length > 0" class="grid">
					<div class="th">
						<button type="submit" class="button">{{ $t('pages.fleets.checkout.next') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { computed, onMounted, ref, watch } from 'vue';
	import dayjs from 'dayjs';
	import { useNow } from '@vueuse/core';
	import { Link, router, useForm, usePage } from '@inertiajs/vue3';
	import { useErrorNotification } from '~/composables/useToast.js';
	import { getConsumption, getDistance, getDuration, getSpeed, getStorage } from '~/utils/fleet.js';
	import { useApiPost } from '~/composables/useApi.js';

	const props = defineProps({
		data: {
			type: Object,
		}
	});

	const data = computed(() => props.data);

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
	const moon = ref();

	const now = useNow({ interval: 1000 });
	const target_time = computed(() => now.value.getTime() + (duration.value * 1000));

	const alliance = ref(0);
	const hold_hours = ref(1);

	const page = usePage();
	const planet = computed(() => page.props.planet);

	const hold = computed(() => {
		let hold = 0;

		if (data.value['mission'] === 5) {
			hold = data.value['ships'].reduce((summ, item) => item['stay'] * hold_hours.value, 0);
		}

		return hold;
	})

	const capacity = computed(() => {
		return storage.value - resource.value.metal - resource.value.crystal - resource.value.deuterium - hold.value;
	})

	onMounted(() => {
		info();
	});

	watch(() => data.value?.target, async () => {
		let ships = {}
		data.value['ships'].forEach((item) => ships[item['id']] = item['count']);

		try {
			const result = await useApiPost('/fleet/checkout', {
				...data.value['target'], ships,
			});

			delete result['target'];

			router.replaceProp('data', Object.assign(data.value, result))

			info();
		} catch (e) {
			useErrorNotification(e.message);
		}
	}, { deep: true });

	function info () {
		distance.value = getDistance(planet.value['coordinates'], data.value['target']);
		maxspeed.value = getSpeed(data.value['ships']);

		duration.value = getDuration({
			factor: speed.value,
			distance: distance.value,
			max_speed: maxspeed.value,
			universe_speed: page.props.speed['fleet']
		});

		consumption.value = getConsumption({
			ships: data.value['ships'],
			duration: duration.value,
			distance: distance.value,
			universe_speed: page.props.speed['fleet']
		});

		storage.value = getStorage(data.value['ships']) - consumption.value;
	}

	function setTarget (galaxy, system, planet, type) {
		data.value['target']['galaxy'] = galaxy
		data.value['target']['system'] = system
		data.value['target']['planet'] = planet

		if (typeof type === 'undefined')
			type = 1

		data.value['target']['planet_type'] = type
	}

	function allianceSet (index) {
		let al = data.value['alliances'][index]

		alliance.value = al['id']
		setTarget(al['galaxy'], al['system'], al['planet'], al['planet_type'])
	}

	function maxRes (type) {
		let current = resource.value.metal + resource.value.crystal + resource.value.deuterium
		current -= resource.value[type]

		let free = storage.value - current

		if (type === 'deuterium') {
			resource.value[type] = Math.max(Math.min(Math.floor(planet.value['resources'][type]['value'] - consumption.value), free), 0)
		} else {
			resource.value[type] = Math.max(Math.min(Math.floor(planet.value['resources'][type]['value']), free), 0)
		}
	}

	function maxResAll () {
		let free = storage.value - Math.floor(planet.value['resources']['metal']['value']) - Math.floor(planet.value['resources']['crystal']['value']) - Math.floor(planet.value['resources']['deuterium']['value'] - consumption.value)

		if (free < 0) {
			resource.value.metal = Math.max(Math.min(Math.floor(planet.value['resources']['metal']['value']), storage.value), 0)
			resource.value.crystal = Math.max(Math.min(Math.floor(planet.value['resources']['crystal']['value']), storage.value - resource.value.metal), 0)
			resource.value.deuterium = Math.max(Math.min(Math.floor(planet.value['resources']['deuterium']['value'] - consumption.value), storage.value - resource.value.metal - resource.value.crystal), 0)
		} else {
			resource.value.metal = Math.max(Math.floor(planet.value['resources']['metal']['value']), 0)
			resource.value.crystal = Math.max(Math.floor(planet.value['resources']['crystal']['value']), 0)
			resource.value.deuterium = Math.max(Math.floor(planet.value['resources']['deuterium']['value'] - consumption.value), 0)
		}
	}

	function clearResAll () {
		resource.value.metal = resource.value.crystal = resource.value.deuterium = 0
	}

	function send() {
		let ships = {};
		data.value.ships.forEach((ship) => ships[ship.id] = ship.count);

		useForm({
			ships,
			...data.value['target'],
			alliance: alliance.value,
			fleet: data.value['fleet'],
			mission: data.value['mission'],
			moon: moon.value,
			speed: speed.value,
			resource: resource.value,
		})
		.post('/fleet/send', {
			onSuccess: (result) => {
				router.push({
					url: '/fleet/send',
					component: 'Fleet/Send',
					props: (currentProps) => ({ ...currentProps, data: result }),
				});
			},
			onError: (errors) => {
				if (errors.exception) {
					useErrorNotification(errors.exception);
				}
			}
		});
	}
</script>