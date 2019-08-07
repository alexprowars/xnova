<template>
	<div class="page-info">
		<div class="page-info-description block">
			<div class="title">{{ $t('TECH.'+page['i']) }}</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col d-flex">
							<div>
								<img v-if="page['i'] < 600" :src="'/images/gebaeude/'+page['i']+'.gif'" class="info" align="top" height="120" width="120" alt="">
								<img v-else-if="page['i'] < 700" :src="'/images/officiers/'+page['i']+'.jpg'" class="info" align="top" height="120" width="120" alt="">
								<img v-else :src="'/images/skin/race'+(page['i'] - 700)+'.gif'" style="float:left;margin:0 20px 10px 0" height="35" width="35" alt="">
							</div>
							<div v-html="page['description']"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<InfoProduction v-if="page['production']" :item="page['i']" :production="page['production']"/>
		<InfoFleet v-if="page['fleet']" :item="page['i']" :fleet="page['fleet']"/>
		<InfoDefence v-if="page['defence']" :item="page['i']" :defence="page['defence']"/>
		<InfoMissile v-if="page['missile']" :item="page['i']" :missile="page['missile']"/>

		<InfoDestroy v-if="page['destroy']" :item="page['i']" :data="page['destroy']"/>
	</div>
</template>

<script>
	import InfoProduction from '~/components/page/info/production.vue'
	import InfoFleet from '~/components/page/info/fleet.vue'
	import InfoDefence from '~/components/page/info/defence.vue'
	import InfoDestroy from '~/components/page/info/destroy.vue'
	import InfoMissile from '~/components/page/info/missile.vue'

	export default {
		name: 'info',
		components: {
			InfoDestroy,
			InfoProduction,
			InfoFleet,
			InfoDefence,
			InfoMissile
		},
		props: {
			popup: {
				type: Object
			}
		},
		async asyncData ({ store })
		{
			const data = await store.dispatch('loadPage')

			return {
				data: data.page
			}
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			return {
				page: {}
			}
		},
		created () {
			this.page = this.popup !== undefined ? this.popup : this.data
		},
	}
</script>