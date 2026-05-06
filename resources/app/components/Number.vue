<template>
	<input v-model="formatValue" type="number" @input="update($event.target.value)">
</template>

<script setup>
	import { ref, watch } from 'vue';

	const modelValue = defineModel();
	const formatValue = ref(modelValue.value);

	watch(formatValue, (value) => {
		formatValue.value = parseInt(value) === 0 ? '' : value;
	}, { immediate: true });

	watch(modelValue, (value) => {
		formatValue.value = value;
	});

	function update (value) {
		value = parseInt(value);

		if (isNaN(value)) {
			value = 0;
		}

		modelValue.value = value;
	}
</script>