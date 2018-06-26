<template>
	<div v-if="page" class="page-info">
		<div class="block">
			<div class="title">{{ $root.getLang('TECH', page['i']) }}</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col d-flex">
							<div>
								<img v-if="page['i'] < 600" :src="$root.getUrl('assets/images/gebaeude/'+page['i']+'.gif')" class="info" align="top" height="120" width="120">
								<img v-else-if="page['i'] < 700" :src="$root.getUrl('assets/images/officiers/'+page['i']+'.jpg')" class="info" align="top" height="120" width="120">
								<img v-else :src="$root.getUrl('assets/images/skin/race'+(page['i'] - 700)+'.gif')" style="float:left;margin:0 20px 10px 0" height="35" width="35">
							</div>
							<div v-html="page['description']"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<info-production v-if="page['production']" :item="page['i']" :production="page['production']"></info-production>
		<info-fleet v-if="page['fleet']" :item="page['i']" :fleet="page['fleet']"></info-fleet>
		<info-defence v-if="page['defence']" :item="page['i']" :defence="page['defence']"></info-defence>
		<info-missile v-if="page['missile']" :item="page['i']" :missile="page['missile']"></info-missile>

		<info-destroy v-if="page['destroy']" :item="page['i']" :data="page['destroy']"></info-destroy>
	</div>
</template>

<script>
	import router from 'router-mixin'
	import InfoProduction from './info-production.vue'
	import InfoFleet from './info-fleet.vue'
	import InfoDefence from './info-defence.vue'
	import InfoDestroy from './info-destroy.vue'
	import InfoMissile from './info-missile.vue'

	export default {
		name: "info",
		mixins: [router],
		components: {
			InfoDestroy,
			InfoProduction,
			InfoFleet,
			InfoDefence,
			InfoMissile
		}
	}
</script>