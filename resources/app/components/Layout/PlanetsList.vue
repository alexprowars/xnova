<template>
	<div class="main-planets">
		<button type="button" :class="{ active }" class="planet-toggle" :aria-label="$t('interface.colonies')" :aria-expanded="active" aria-controls="game-planets" @click="emit('toggle')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</button>
		<div id="game-planets" :class="{ active }" class="planet-sidebar">
			<div class="sidebar-caption">{{ $t('interface.colonies') }} <span>{{ items.length }}</span></div>
			<div class="list">
				<PlanetRow v-for="item in items" :key="item['id']" :item="item"></PlanetRow>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import PlanetRow from './PlanetsListRow.vue';
	import { computed } from 'vue';

	defineProps({
		active: {
			type: Boolean,
			default: true,
		}
	});

	const state = useState();
	const emit = defineEmits(['toggle']);

	const items = computed(() => {
		return state.user.planets || [];
	})
</script>