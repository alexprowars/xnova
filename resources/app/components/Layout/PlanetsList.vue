<template>
	<div class="main-planets">
		<a :class="{ active }" class="planet-toggle" @click.prevent="emit('toggle')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>
		<div :class="{ active }" class="planet-sidebar">
			<div class="list">
				<PlanetRow v-for="item in items" :key="item['id']" :item="item"></PlanetRow>
			</div>
		</div>
	</div>
</template>

<script setup>
	import PlanetRow from './PlanetsListRow.vue';
	import { computed } from 'vue';
	import { usePage } from '@inertiajs/vue3';

	defineProps({
		active: {
			type: Boolean,
			default: true,
		}
	});

	const page = usePage();
	const emit = defineEmits(['toggle']);

	const items = computed(() => {
		return page.props.user.planets || [];
	})
</script>