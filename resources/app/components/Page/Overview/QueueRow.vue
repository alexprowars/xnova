<template>
	<div class="grid grid-cols-6">
		<div class="th col-span-2 sm:col-span-1 text-center">
			<div class="z">
				<Timer :value="item['date']"/>
			</div>
			<span class="positive sm:hidden">{{ $formatDate(item['date'], 'DD MMM HH:mm:ss') }}</span>
		</div>
		<div class="th col-span-4 sm:col-span-5">
			<span class="flight owndeploy">
				<a v-if="item['planet_id'] !== planet.id" href="" @click.prevent="changePlanet(item['planet_id'])" style="color:#33ff33;">{{ planetItem?.['name'] }}</a><span v-else>{{ planetItem?.['name'] }}</span>:
			</span>
			<span v-if="item['level']" class="holding colony">{{ $t('tech.' + item['item']) }} ({{ item['level'] }})</span>
			<span v-if="item['count']" class="holding colony">{{ $t('tech.' + item['item']) }} ({{ item['count'] }})</span>
			<span class="positive float-sm-end hidden sm:inline">{{ $formatDate(item['date'], 'DD MMM HH:mm:ss') }}</span>
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { usePage } from '@inertiajs/vue3';
	import Timer from '../../Timer.vue';
	import { changePlanet } from '../../../utils/helpers.js';

	const { item } = defineProps({
		item: Object
	});

	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);

	const planetItem = computed(() => {
		return user.value['planets'].find((p) => p['id'] === item['planet_id']);
	});
</script>