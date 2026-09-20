<template>
	<Head :title="$t('menu.empire')"/>
	<div class="page-empire">
		<div class="empire-heading">
			<div><h1>{{ $t('pages.empire.title') }}</h1><span>{{ $t('pages.empire.colony_count', { count: page.planets.length }) }}</span></div>
			<div class="empire-account"><span>{{ $t('pages.empire.planet_fields') }} <strong>{{ $formatNumber(total.fields) }} / {{ $formatNumber(total.fields_max) }}</strong></span><span><CreditsIcon aria-hidden="true" focusable="false"/>{{ $t('credits') }}: {{ $formatNumber(user.credits) }}</span></div>
		</div>

		<div class="empire-summary" :aria-label="$t('pages.empire.empire_total')">
			<div v-for="res in resourceTypes" :key="res" class="empire-summary-item" :class="res">
				<component :is="resourceIcons[res]" aria-hidden="true" focusable="false"/>
				<div><span>{{ $t('resources.' + res) }}</span><strong :class="{ negative: res === 'energy' && total.resources[res] < 0 }">{{ $formatNumber(total.resources[res]) }}</strong></div>
			</div>
		</div>

		<UiTabs v-model="activeSection" :items="tabs" :label="$t('pages.empire.section')" unmount-on-hide>
			<template #panel>
				<template v-if="activeSection !== 'tech'">
					<div class="empire-toolbar">
						<div class="empire-search"><label for="empire-search">{{ $t('pages.empire.search') }}</label><input id="empire-search" type="search" v-model="search" :placeholder="$t('pages.empire.search_placeholder')"></div>
						<div v-if="isElementSection" class="empire-object-select"><label for="empire-object">{{ $t('pages.empire.compare_object') }}</label><select id="empire-object" v-model="selectedElements[activeSection]"><option v-for="id in elementIds" :key="id" :value="id">{{ $t('tech.' + id) }}</option></select></div>
					</div>

					<div v-if="isElementSection" class="empire-comparison-heading"><img :src="'/assets/images/elements/' + selectedElement + '.webp'" alt="" width="30" height="30"><strong>{{ $t('tech.' + selectedElement) }}</strong><span>{{ $t('pages.empire.compare_hint') }}</span></div>

					<div v-if="filteredPlanets.length" ref="tableScroll" class="table-responsive empire-table-scroll" tabindex="0" :aria-label="$t('pages.empire.comparison')">
						<table class="table empire-table" :class="{ 'empire-element-table': isElementSection }">
							<thead><tr>
								<th scope="col" class="empire-planet-column">{{ $t('pages.empire.planet_name') }}</th>
								<template v-if="activeSection === 'resources'"><th scope="col">{{ $t('pages.empire.planet_fields') }}</th><th v-for="res in resourceTypes" :key="res" scope="col"><span class="empire-resource-label" :class="res"><component :is="resourceIcons[res]" aria-hidden="true" focusable="false"/>{{ $t('resources.' + res) }}</span></th></template>
								<template v-else-if="activeSection === 'production'"><th v-for="res in materials" :key="res" scope="col"><span class="empire-resource-label" :class="res"><component :is="resourceIcons[res]" aria-hidden="true" focusable="false"/>{{ $t('resources.' + res) }}</span></th></template>
								<template v-else-if="activeSection === 'factors'"><th v-for="id in productionIds" :key="id" scope="col">{{ $t('tech.' + id) }}</th></template>
								<template v-else><th scope="col">{{ $t(activeSection === 'buildings' ? 'pages.empire.level' : 'pages.empire.available') }}</th><th scope="col">{{ $t(activeSection === 'buildings' ? 'pages.empire.queued_level' : 'pages.empire.queued') }}</th><th v-if="activeSection === 'fleet'" scope="col">{{ $t('pages.empire.in_flight') }}</th></template>
							</tr></thead>
							<tbody><tr v-for="planet in filteredPlanets" :key="planet.id" :class="{ 'is-current': planet.id === state.planet?.id }">
								<th scope="row" class="empire-planet-column"><div class="empire-planet"><button type="button" class="empire-planet-image" @click="toPlanet(planet.id)" :aria-label="planet.name"><img :src="'/assets/images/planeten/small/s_' + planet.image + '.jpg'" alt="" width="32" height="32" loading="lazy"></button><div><button type="button" class="empire-planet-name" @click="toPlanet(planet.id)">{{ planet.name }}</button><Link class="empire-coordinates" :href="'/galaxy?galaxy=' + planet.position.galaxy + '&system=' + planet.position.system">[{{ planet.position.galaxy }}:{{ planet.position.system }}:{{ planet.position.planet }}]</Link></div></div></th>
								<template v-if="activeSection === 'resources'"><td :class="{ negative: planet.fields >= planet.fields_max }">{{ planet.fields }} / {{ planet.fields_max }}</td><td v-for="res in resourceTypes" :key="res" :class="resourceClass(planet, res)">{{ $formatNumber(planet.resources[res].value) }}</td></template>
								<template v-else-if="activeSection === 'production'"><td v-for="res in materials" :key="res" :class="{ negative: planet.resources[res].production < 0 }">{{ $formatNumber(planet.resources[res].production) }}</td></template>
								<template v-else-if="activeSection === 'factors'"><td v-for="id in productionIds" :key="id" :class="planet.factor[id] >= 100 ? 'positive' : 'negative'">{{ $formatNumber(planet.factor[id]) }}%</td></template>
								<template v-else><td>{{ $formatNumber(planet.elements[selectedElement]?.value || 0) }}</td><td class="empire-queued">{{ planet.elements[selectedElement]?.build > 0 ? $formatNumber(planet.elements[selectedElement].build) : '—' }}</td><td v-if="activeSection === 'fleet'" class="empire-in-flight">{{ planet.elements[selectedElement]?.fly > 0 ? $formatNumber(planet.elements[selectedElement].fly) : '—' }}</td></template>
							</tr></tbody>
							<tfoot v-if="activeSection !== 'factors'"><tr>
								<th scope="row" class="empire-planet-column">{{ $t(activeSection === 'buildings' ? 'pages.empire.empire_max' : 'pages.empire.empire_total') }}</th>
								<template v-if="activeSection === 'resources'"><td>{{ $formatNumber(total.fields) }} / {{ $formatNumber(total.fields_max) }}</td><td v-for="res in resourceTypes" :key="res">{{ $formatNumber(total.resources[res]) }}</td></template>
								<template v-else-if="activeSection === 'production'"><td v-for="res in materials" :key="res">{{ $formatNumber(total.production[res]) }}</td></template>
								<template v-else><td>{{ $formatNumber(total.elements[selectedElement]?.value || 0) }}</td><td class="empire-queued">{{ total.elements[selectedElement]?.build > 0 ? $formatNumber(total.elements[selectedElement].build) : '—' }}</td><td v-if="activeSection === 'fleet'" class="empire-in-flight">{{ total.elements[selectedElement]?.fly > 0 ? $formatNumber(total.elements[selectedElement].fly) : '—' }}</td></template>
							</tr></tfoot>
						</table>
					</div>
					<div v-else class="empire-empty">{{ $t('pages.empire.no_planets') }}</div>

				</template>

				<div v-else class="empire-research">
					<div class="empire-research-note">{{ $t('pages.empire.research_scope') }}</div>
					<div v-if="page.tech.length" class="empire-research-grid"><div v-for="item in page.tech" :key="item.id" class="empire-research-card"><img :src="'/assets/images/elements/' + item.id + '.webp'" alt="" width="38" height="38" loading="lazy"><div><strong>{{ $t('tech.' + item.id) }}</strong><span>{{ $t('pages.empire.level') }} {{ item.value }}</span><span v-if="item.build > 0" class="empire-queued">{{ $t('pages.empire.queued_level') }}: {{ item.build }}</span></div></div></div>
					<div v-else class="empire-empty">{{ $t('pages.empire.no_research') }}</div>
				</div>
			</template>
		</UiTabs>
	</div>
</template>

<script setup>
	import { UiTabs } from '~/components/UI';
	import useState from '~/composables/useState.js';
	import { computed, ref, watch } from 'vue';
	import MetalIcon from '~/images/icons/resources/metal.svg?component';
	import CrystalIcon from '~/images/icons/resources/crystal.svg?component';
	import DeuteriumIcon from '~/images/icons/resources/deuterium.svg?component';
	import EnergyIcon from '~/images/icons/resources/energy.svg?component';
	import CreditsIcon from '~/images/icons/resources/credits.svg?component';
	import { Head, Link, router } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { changePlanet as changePlanetFn } from '~/utils/helpers.js';

	const resourceIcons = {
		metal: MetalIcon,
		crystal: CrystalIcon,
		deuterium: DeuteriumIcon,
		energy: EnergyIcon,
	};

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const { t, tm } = useI18n();
	const state = useState();
	const user = computed(() => state.user);

	const materials = ['metal', 'crystal', 'deuterium'];
	const resourceTypes = [...materials, 'energy'];
	const productionIds = [1, 2, 3, 4, 12, 212];
	const sections = [
		{ id: 'resources', label: 'resources' },
		{ id: 'production', label: 'production_per_hour' },
		{ id: 'factors', label: 'efficiency' },
		{ id: 'buildings', label: 'list_buildings' },
		{ id: 'fleet', label: 'list_fleets' },
		{ id: 'defense', label: 'list_defense' },
		{ id: 'tech', label: 'list_techs' },
	];
	const tabs = computed(() => sections.map(section => ({ id: section.id, label: t('pages.empire.' + section.label) })));
	const activeSection = ref('resources');
	const selectedElements = ref({ buildings: '1', fleet: '202', defense: '401' });
	const search = ref('');
	const tableScroll = ref(null);
	const isElementSection = computed(() => ['buildings', 'fleet', 'defense'].includes(activeSection.value));
	const selectedElement = computed(() => selectedElements.value[activeSection.value]);
	const elementIds = computed(() => Object.keys(tm('tech')).filter(id => {
		if (activeSection.value === 'buildings') return id < 100;
		if (activeSection.value === 'fleet') return id > 200 && id < 300;
		if (activeSection.value === 'defense') return id > 400 && id < 600;
		return false;
	}));
	const filteredPlanets = computed(() => {
		const query = search.value.trim().toLocaleLowerCase().replace(/[\[\]\s]/g, '');

		return props.page.planets.filter(planet => {
			const coordinates = [planet.position.galaxy, planet.position.system, planet.position.planet].join(':');
			return (planet.name.toLocaleLowerCase().replace(/\s/g, '') + ' ' + coordinates).includes(query);
		});
	});

	watch([search, activeSection], () => {
		if (tableScroll.value) {
			tableScroll.value.scrollTop = 0;
			tableScroll.value.scrollLeft = 0;
		}
	}, { flush: 'post' });

	function resourceClass(planet, resource) {
		const value = planet.resources[resource];
		return (resource === 'energy' ? value.value >= 0 : value.value < value.storage) ? 'positive' : 'negative';
	}

	const total = computed(() => {
		let result = {
			fields: 0,
			fields_max: 0,
			resources: {},
			production: {},
			elements: {},
		};

		let resources = Object.keys(tm('resources'));
		let elements = Object.keys(tm('tech'));

		for (let res of resources) {
			result.resources[res] = 0
			result.production[res] = 0
		}

		for (let id of elements) {
			result.elements[id] = {
				value: 0,
				build: 0,
				fly: 0
			};
		}

		props.page['planets'].forEach((planet) => {
			result.fields += planet['fields']
			result.fields_max += planet['fields_max']

			for (let res of resources) {
				if (typeof planet['resources'][res] === 'undefined') {
					return;
				}

				result.resources[res] += planet['resources'][res]['value']
				result.production[res] += planet['resources'][res]['production']
			}

			for (let id of elements) {
				if (typeof planet['elements'][id] === 'undefined') {
					continue;
				}

				if (id < 100) {
					if (result.elements[id].value < planet['elements'][id]['value'])
						result.elements[id].value = planet['elements'][id]['value']

					if (result.elements[id].build < planet['elements'][id]['build'])
						result.elements[id].build = planet['elements'][id]['build']
				} else if (id > 200 && id < 300) {
					result.elements[id].value += planet['elements'][id]['value']
					result.elements[id].build += planet['elements'][id]['build']
					result.elements[id].fly += planet['elements'][id]['fly']
				} else if (id > 400 && id < 600) {
					result.elements[id].value += planet['elements'][id]['value']
					result.elements[id].build += planet['elements'][id]['build']
				}
			}
		})

		for (let id of elements) {
			if (id < 100) {
				if (result.elements[id].value > result.elements[id].build - 1)
					result.elements[id].build = 0
			}
		}

		return result;
	});

	async function toPlanet(id) {
		await changePlanetFn(id);

		router.visit('/overview');
	}
</script>