<template>
	<RadioGroupRoot v-model="selected" orientation="horizontal" class="ui-tab-list" :aria-label="label">
		<RadioGroupItem v-for="item in items" :key="item.id" :value="item.id" :disabled="item.disabled" class="ui-tab-trigger">
			<span v-if="item.color" class="ui-tab-dot" :style="{ backgroundColor: item.color }" aria-hidden="true"/>
			{{ item.label }}
		</RadioGroupItem>
	</RadioGroupRoot>
</template>

<script setup>
	import { RadioGroupItem, RadioGroupRoot } from 'reka-ui';

	defineProps({
		items: {
			type: Array,
			required: true,
		},
		label: {
			type: String,
			required: true,
		},
	});

	const selected = defineModel({ type: [String, Number], required: true });
</script>
