<template>
	<TabsRoot v-model="active" :unmount-on-hide="unmountOnHide" class="ui-tabs">
		<TabsList class="ui-tab-list" :aria-label="label">
			<TabsTrigger v-for="item in items" :key="item.id" :value="item.id" :disabled="item.disabled" class="ui-tab-trigger">
				{{ item.label }}
			</TabsTrigger>
		</TabsList>
		<TabsContent v-for="item in items" :key="item.id" :value="item.id" class="ui-tab-panel">
			<TabPanelContent :item="item" :content="slots[String(item.id)] || slots.panel"/>
		</TabsContent>
	</TabsRoot>
</template>

<script setup>
	import { computed, useSlots, watchEffect } from 'vue';
	import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
	import TabPanelContent from './TabPanelContent.js';

	const props = defineProps({
		items: { type: Array, required: true },
		label: { type: String, required: true },
		unmountOnHide: Boolean,
	});

	const active = defineModel({ type: [String, Number] });
	const slots = useSlots();
	const available = computed(() => props.items.filter(item => !item.disabled));

	watchEffect(() => {
		if (!available.value.some(item => item.id === active.value)) {
			active.value = available.value[0]?.id;
		}
	});
</script>
