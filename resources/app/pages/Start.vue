<template>
	<Head :title="$t('pages.start.title')"/>
	<div class="page-start">
		<SelectAvatar v-if="!user.sex || !user.avatar"/>
		<SelectRace v-else-if="!user.race"/>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import SelectRace from '~/components/Page/Start/SelectRace.vue';
	import SelectAvatar from '~/components/Page/Start/SelectAvatar.vue';
	import { Head, router } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				header: false,
				menu: false,
				resources: false,
				planets: false,
			}
		}
	});

	const state = useState();
	const user = computed(() => state.user);

	if (user.value.race && user.value.sex && user.value.avatar) {
		router.visit('/overview');
	}
</script>