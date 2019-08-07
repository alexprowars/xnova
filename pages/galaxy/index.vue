<template>
	<div v-if="page" class="page-galaxy">
		<GalaxySelector :shortcuts="page['shortcuts']" :galaxy="page['galaxy']" :system="page['system']"></GalaxySelector>
		<div class="separator"></div>

		<MissileAttack v-if="missile" :page="page" :planet="missilePlanet" @close="missile = false"></MissileAttack>

		<div class="table-responsive">
			<table class="table galaxy">
				<tbody>
					<tr>
						<td class="c" colspan="9">Солнечная система {{ page['galaxy'] }}:{{ page['system'] }}</td>
					</tr>
					<tr>
						<td class="c">№</td>
						<td class="c">&nbsp;</td>
						<td class="c">Планета</td>
						<td class="c">&nbsp;</td>
						<td class="c">ПО</td>
						<td class="c">Игрок</td>
						<td class="c">&nbsp;</td>
						<td class="c">Альянс</td>
						<td class="c">Действия</td>
					</tr>

					<tr is="galaxy-row" v-for="(item, index) in page['items']"
						:key="index"
						:item="item"
						:user="page['user']"
						:galaxy="page['galaxy']"
						:system="page['system']"
						:planet="index + 1"
						@sendMissile="sendMissile(item['planet'])"
					></tr>

					<tr v-if="page['user']['allowExpedition']">
						<th width="30">16</th>
						<th colspan="8" class="c big">
							<nuxt-link :to="'/fleet/?galaxy='+page['galaxy']+'&system='+page['system']+'&planet=16&mission=15'">неизведанные дали</nuxt-link>
						</th>
					</tr>
					<tr>
						<td class="c" colspan="6">
							<span v-if="planets === 0">нет заселённых планет</span>
							<span v-else>{{ planets }} {{ planets | morph('заселённая планета', 'заселённые планеты', 'заселённых планет') }}</span>
						</td>
						<td class="c" colspan=3>
							<Popper>
								<GalaxyLegend/>
								<template slot="reference">
									Легенда
								</template>
							</Popper>
						</td>
					</tr>
					<tr>
						<td class="c" colspan="3">{{ page['user']['interplanetary_misil'] }} {{ page['user']['interplanetary_misil'] | morph('ракета', 'ракеты', 'ракет') }}</td>
						<td class="c" colspan="3">{{ page['user']['fleets'] }} / {{ page['user']['max_fleets'] }} {{ page['user']['fleets'] | morph('флот', 'флота', 'флотов') }}</td>
						<td class="c" colspan="3">
							<div>{{ page['user']['recycler'] | number }} {{ page['user']['recycler'] | morph('переработчик', 'переработчика', 'переработчиков') }}</div>
							<div>{{ page['user']['spy_sonde'] | number }} {{ page['user']['spy_sonde'] | morph('шпионский зонд', 'шпионских зонда', 'шпионских зондов') }}</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
	import GalaxyRow from '~/components/page/galaxy/row.vue'
	import GalaxySelector from '~/components/page/galaxy/selector.vue'
	import GalaxyLegend from '~/components/page/galaxy/legend.vue'
	import MissileAttack from '~/components/page/galaxy/missile-attack.vue'

	export default {
		name: 'galaxy',
		components: {
			GalaxyRow,
			GalaxySelector,
			GalaxyLegend,
			MissileAttack,
		},
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			return {
				missile: false,
				missilePlanet: 0,
			}
		},
		computed: {
			planets ()
			{
				if (!this.page.items)
					return 0;

				let count = 0;

				this.page.items.forEach((item) =>
				{
					if (item !== false)
						count++;
				});

				return count;
			}
		},
		methods: {
			sendMissile (planet)
			{
				this.missile = true;
				this.missilePlanet = planet;
			}
		}
	}
</script>