<template>
	<Head title="Галактика"/>
	<div class="page-galaxy">
		<GalaxySelector
			:shortcuts="data['shortcuts']"
			:galaxy="data['galaxy']"
			:galaxy-max="data['galaxy_max']"
			:system="data['system']"
			:system-max="data['system_max']"
			@change="changeCoordinates"
		/>

		<MissileAttack v-if="missile" :page="data" :planet="missilePlanet" @close="missile = false"/>

		<div class="block">
			<div class="title">
				{{ $t('pages.galaxy.title', [data['galaxy'], data['system']]) }}
			</div>
			<div class="content">
				<div class="table-responsive">
					<table class="table galaxy text-center">
						<tbody>
							<tr>
								<td class="c" width="35">№</td>
								<td class="c" width="34">&nbsp;</td>
								<td class="c">{{ $t('pages.galaxy.column_planet') }}</td>
								<td class="c" width="34">&nbsp;</td>
								<td class="c" width="30">{{ $t('pages.galaxy.column_debris') }}</td>
								<td class="c" width="180">{{ $t('pages.galaxy.column_player') }}</td>
								<td class="c" width="30">&nbsp;</td>
								<td class="c" width="100">{{ $t('pages.galaxy.column_alliance') }}</td>
								<td class="c" width="135">{{ $t('pages.galaxy.column_actions') }}</td>
							</tr>

							<GalaxyRow v-for="(item, index) in rows"
								:key="data['galaxy'] + ':' + data['system'] + ':' + index"
								:item="item"
								:user="data['user']"
								:galaxy="data['galaxy']"
								:system="data['system']"
								:planet="index + 1"
								@sendMissile="sendMissile(item['planet'])"
							/>

							<tr v-if="user['technology']['expedition_tech']">
								<td class="th" width="30">16</td>
								<td class="c big" colspan="8">
									<Link :href="'/fleet?galaxy=' + data['galaxy'] + '&system=' + data['system'] + '&planet=16&mission=15'">
										{{ $t('pages.galaxy.planet_16') }}
									</Link>
								</td>
							</tr>
							<tr>
								<td class="c" colspan="6">
									{{ $t('pages.galaxy.no_planets', data.items.length) }}
								</td>
								<td class="c" colspan="3">
									<Popper>
										<template #content>
											<GalaxyLegend/>
										</template>
										<span>{{ $t('pages.galaxy.legend_text') }}</span>
									</Popper>
								</td>
							</tr>
							<tr>
								<td class="c" colspan="3">{{ $t('pages.galaxy.rockets', planet['units']['interplanetary_misil']) }}</td>
								<td class="c" colspan="3">{{ data['user']['fleets'] }} / {{ $t('pages.galaxy.fleets', user['fleets_max']) }}</td>
								<td class="c" colspan="3">
									<div>{{ $t('pages.galaxy.recyclers', planet['units']['recycler']) }}</div>
									<div>{{ $t('pages.galaxy.spy_probes', planet['units']['spy_sonde']) }}</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import GalaxyRow from '~/components/Page/Galaxy/Row.vue';
	import GalaxySelector from '~/components/Page/Galaxy/Selector.vue';
	import GalaxyLegend from '~/components/Page/Galaxy/Legend.vue';
	import MissileAttack from '~/components/Page/Galaxy/MissileAttack.vue';
	import { computed, ref } from 'vue';
	import Popper from '~/components/Popper.vue';
	import { Head, Link, router } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: {
			type: Object,
		}
	});

	const missile = ref(false);
	const missilePlanet = ref(0);

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	const rows = computed(() => {
		let result = [];

		for (let i = 1; i <= 15; i++) {
			result.push(props.data.items.find(item => item.position.planet === i) || null);
		}

		return result;
	});

	function sendMissile (planet) {
		missile.value = true
		missilePlanet.value = planet
	}

	function changeCoordinates(value) {
		router.visit('/galaxy', {
			data: value,
		});
	}
</script>