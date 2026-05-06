<template>
	<form method="post" class="block-table" @submit.prevent="create">
		<div class="grid">
			<div class="th">
				<input type="text" v-model="name" size="50">
				<br>
				<button type="submit">{{ $t('pages.fleets.verband.create_submit') }}</button>
			</div>
		</div>
	</form>
</template>

<script setup>
	import { refreshNuxtData, useApiSubmit } from '#imports';
	import { ref } from 'vue';

	const props = defineProps({
		id: Number,
	});

	const name = ref('AKS' + rand(100000, 999999999));

	function create() {
		useApiSubmit('/fleet/verband/' + props['id'], {
			name: name.value,
		}, () => {
			refreshNuxtData();
		});
	}

	function rand (min, max) {
		return Math.floor(Math.random() * (max - min + 1)) + min;
	}
</script>