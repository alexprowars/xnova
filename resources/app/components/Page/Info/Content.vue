<template>
	<div class="page-info">
		<div class="block">
			<div class="title info-description-heading">
				<span>{{ item['name'] }}</span>
				<Link v-if="item.id < 600" :href="'/tech/' + item.id" class="info-tree-link">{{ $t('pages.techtree.requirement_tree') }} <span aria-hidden="true">↗</span></Link>
			</div>
			<div class="content info-description-body is-padded">
				<div class="info-description-image">
					<img v-if="item['id'] < 600" :src="'/assets/images/elements/' + item['id'] + '.webp'" class="info" height="150" width="150" alt="">
					<img v-else-if="item['id'] < 700" :src="'/assets/images/officiers/' + item['id'] + '.jpg'" class="info" height="120" width="120" alt="">
					<img v-else :src="'/assets/images/skin/race' + (item['id'] - 700) + '.gif'" class="info-race-image" height="35" width="35" alt="">
				</div>
				<div class="info-description-text" v-html="item['description']"></div>
			</div>
		</div>

		<InfoProduction v-if="item['production']" :item="item['id']" :production="item['production']"/>
		<InfoCombat v-if="item['combat']" :item="item['id']" :data="item['combat']"/>
		<InfoMissile v-if="item['id'] === 44" :item="item['id']"/>
		<InfoAlliance v-if="item['alliance']" :item="item['id']" :data="item['alliance']"/>

		<InfoDestroy v-if="item['destroy']" :item="item['id']" :data="item['destroy']"/>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
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