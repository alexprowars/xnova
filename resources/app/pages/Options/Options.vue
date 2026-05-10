<template>
	<Head title="Hacтpoйки"/>
	<div>
		<VacationTab v-if="user.vacation"/>
		<OptionsTab v-else :data="data"/>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import VacationTab from '~/components/Page/Options/VacationTab.vue';
	import OptionsTab from '~/components/Page/Options/OptionsTab.vue';
	import { Head } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: Object,
	})

	const state = useState();
	const user = computed(() => state.user);
</script>