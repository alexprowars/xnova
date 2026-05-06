<template>
	<form method="post" @submit.prevent="update">
		<input type="text" name="name" v-model="name" size="50">
		<br>
		<button type="submit">{{ $t('pages.fleets.verband.change_name_submit') }}</button>
	</form>
</template>

<script setup>
	import { ref } from 'vue';
	import { refreshNuxtData, useApiSubmit } from '#imports';

	const props = defineProps({
		id: Number,
		name: String,
	});

	const name = ref(props.name);

	function update() {
		useApiSubmit('/fleet/verband/' + props['id'] + '/name', {
			name: name.value,
		}, () => {
			refreshNuxtData();
		});
	}
</script>