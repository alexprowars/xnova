<template>
	<div class="tabs-component">
		<ul role="tablist" class="tabs-component-tabs">
			<li v-for="(tab, i) in tabs" :key="i" :class="{active: activeTab === tab.hash}" class="tabs-component-tab" role="presentation">
				<a v-html="tab.header" @click.prevent="selectTab(tab.hash)" :href="tab.hash" class="tabs-component-tab-a" role="tab"></a>
			</li>
		</ul>
		<div class="tabs-component-panels">
			<slot></slot>
		</div>
	</div>
</template>

<script setup>
	import { ref, provide  } from 'vue';

	const tabs = ref([]);
	const activeTab = ref('');
	const emit = defineEmits(['changed']);

	provide('addTab', (tab) => {
		const count = tabs.value.push(tab);

		if (count === 1) {
			activeTab.value = tab.hash;
		}
	});

	provide('activeTab', activeTab);

	function selectTab (selectedTabHash) {
		const selectedTab = tabs.value.find(tab => tab.hash === selectedTabHash);

		if (!selectedTab)
			return;

		activeTab.value = selectedTabHash;

		emit('changed', selectedTabHash);
	}
</script>