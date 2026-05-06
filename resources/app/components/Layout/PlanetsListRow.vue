<template>
	<div class="planet" :class="['type_' + item.planet_type, (planet.id === item.id ? 'current' : '')]"  @click.prevent="changePlanet">
		<div class="planet-image" :title="item.name">
			<img :src="'/assets/images/planeten/small/s_' + item.image + '.jpg'" height="40" width="40" :alt="item.name">
		</div>
		<div class="planet-name">
			<div>{{ item.name }}</div>
			<span>
				<PlanetLink :galaxy="item.galaxy" :system="item.system" :planet="item.planet"/>
			</span>
		</div>
	</div>
</template>

<script setup>
	import { router, usePage } from '@inertiajs/vue3';
	import { computed } from 'vue';
	import PlanetLink from '../PlanetLink.vue';
	import { useApiPost } from '../../composables/useApi.js';
	import { useErrorNotification } from '../../composables/useToast.js';
	import { changePlanet as changePlanetFn } from '../../utils/helpers.js';

	const { item } = defineProps({
		item: {
			type: Object
		}
	});

	const page = usePage();
	const planet = computed(() => page.props.planet);

	function changePlanet () {
		if (planet.value.id === item.id) {
			return;
		}

		changePlanetFn(item.id);
	}
</script>