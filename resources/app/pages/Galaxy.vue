<template>
	<Head :title="$t('menu.galaxy')"/>
	<div class="page-galaxy">
		<GalaxySelector
			:shortcuts="page['shortcuts']"
			:galaxy="page['galaxy']"
			:galaxy-max="page['galaxy_max']"
			:system="page['system']"
			:system-max="page['system_max']"
			@change="changeCoordinates"
		/>

		<MissileAttack v-if="missile" :target="missile" @close="missile = null"/>

		<div class="block">
			<div class="title galaxy-system-title">
				<GalaxyIcon type="system"/>
				{{ $t('pages.galaxy.title', [page['galaxy'], page['system']]) }}
			</div>
			<div class="content">
				<div class="table-responsive">
					<table class="table galaxy text-center">
						<thead>
							<tr>
								<th scope="col" class="c" width="35">№</th>
								<th scope="col" class="c" width="34"><span class="sr-only">{{ $t('pages.galaxy.column_planet') }}</span></th>
								<th scope="col" class="c">{{ $t('pages.galaxy.column_planet') }}</th>
								<th scope="col" class="c" width="34"><span class="sr-only">{{ $t('planet_type.3') }}</span></th>
								<th scope="col" class="c" width="30">{{ $t('pages.galaxy.column_debris') }}</th>
								<th scope="col" class="c" width="180">{{ $t('pages.galaxy.column_player') }}</th>
								<th scope="col" class="c" width="30"><span class="sr-only">{{ $t('pages.overview.fraction') }}</span></th>
								<th scope="col" class="c" width="100">{{ $t('pages.galaxy.column_alliance') }}</th>
								<th scope="col" class="c" width="135">{{ $t('pages.galaxy.column_actions') }}</th>
							</tr>
						</thead>
						<tbody>

							<GalaxyRow v-for="(item, index) in rows"
								:key="page['galaxy'] + ':' + page['system'] + ':' + index"
								:item="item"
								:user="page['user']"
								:galaxy="page['galaxy']"
								:system="page['system']"
								:planet="index + 1"
								@sendMissile="missile = item.position"
							/>

							<tr v-if="user['technology']['expedition_tech']" class="galaxy-expedition">
								<td class="th" width="30">16</td>
								<td class="c big" colspan="8">
									<Link :href="'/fleet?galaxy=' + page['galaxy'] + '&system=' + page['system'] + '&planet=16&mission=15'">
										{{ $t('pages.galaxy.planet_16') }}
									</Link>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td class="c" colspan="6">
									{{ $t('pages.galaxy.no_planets', page.items.length) }}
								</td>
								<td class="c" colspan="3">
									<Popper popper-class="galaxy-tooltip">
										<template #content>
											<GalaxyLegend/>
										</template>
										<button type="button" class="galaxy-legend-toggle">{{ $t('pages.galaxy.legend_text') }}</button>
									</Popper>
								</td>
							</tr>
							<tr class="galaxy-capacity">
								<td class="c" colspan="3">{{ $t('pages.galaxy.rockets', planet['units']['interplanetary_misil']) }}</td>
								<td class="c" colspan="3">{{ page['user']['fleets'] }} / {{ $t('pages.galaxy.fleets', user['fleets_max']) }}</td>
								<td class="c" colspan="3">
									<div>{{ $t('pages.galaxy.recyclers', planet['units']['recycler']) }}</div>
									<div>{{ $t('pages.galaxy.spy_probes', planet['units']['spy_sonde']) }}</div>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import GalaxyRow from '~/components/Page/Galaxy/Row.vue';
	import GalaxyIcon from '~/components/Page/Galaxy/GalaxyIcon.vue';
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
		page: Object,
	});

	const missile = ref(null);

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);

	const rows = computed(() => {
		let result = [];

		for (let i = 1; i <= 15; i++) {
			result.push(props.page.items.find(item => item.position.planet === i) || null);
		}

		return result;
	});

	function changeCoordinates(value) {
		router.visit('/galaxy', {
			data: value,
		});
	}
</script>