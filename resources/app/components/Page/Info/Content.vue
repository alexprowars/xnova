<template>
	<div class="page-info">
		<div class="page-info-description block">
			<div class="title">{{ item['name'] }}</div>
			<div class="content flex gap-2 p-2">
				<div>
					<img v-if="item['id'] < 600" :src="'/assets/images/elements/' + item['id'] + '.webp'" class="info" height="150" width="150" alt="">
					<img v-else-if="item['id'] < 700" :src="'/assets/images/officiers/' + item['id'] + '.jpg'" class="info" height="120" width="120" alt="">
					<img v-else :src="'/assets/images/skin/race' + (item['id'] - 700) + '.gif'" style="float:left;margin:0 20px 10px 0" height="35" width="35" alt="">
				</div>
				<div v-html="item['description']"></div>
			</div>
		</div>

		<InfoProduction v-if="item['production']" :item="item['id']" :production="item['production']"/>
		<InfoCombat v-if="item['combat']" :item="item['id']" :data="item['combat']"/>
		<InfoMissile v-if="item['missile']" :item="item['id']"/>
		<InfoAlliance v-if="item['alliance']" :item="item['id']" :data="item['alliance']"/>

		<InfoDestroy v-if="item['destroy']" :item="item['id']" :data="item['destroy']"/>
	</div>
</template>

<script setup>
	import InfoProduction from './Production.vue';
	import InfoCombat from './Combat.vue';
	import InfoDestroy from './Destroy.vue';
	import InfoMissile from './Missile.vue';
	import InfoAlliance from './Alliance.vue';

	defineProps({
		item: {
			type: Object
		}
	})
</script>