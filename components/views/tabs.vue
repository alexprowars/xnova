<template>
	<div class="tabs-component">
		<ul role="tablist" class="tabs-component-tabs">
			<li v-for="(tab, i) in tabs" :key="i" :class="{active: tab.active}" class="tabs-component-tab" role="presentation">
				<a v-html="tab.header" @click.prevent="selectTab(tab.hash)" :href="tab.hash" class="tabs-component-tab-a" role="tab"></a>
			</li>
		</ul>
		<div class="tabs-component-panels">
			<slot></slot>
		</div>
	</div>
</template>

<script>
	export default {
		data: () => ({
			tabs: [],
		}),
		created () {
			this.tabs = this.$children;
		},
		mounted ()
		{
			if (this.tabs.length)
				this.selectTab(this.tabs[0].hash);
		},
		methods:
		{
			findTab (hash) {
				return this.tabs.find(tab => tab.hash === hash);
			},
			selectTab (selectedTabHash)
			{
				const selectedTab = this.findTab(selectedTabHash);

				if (!selectedTab)
					return;

				this.tabs.forEach(tab => {
					tab.active = (tab.hash === selectedTab.hash);
				});

				this.$emit('changed', { tab: selectedTab });
			}
		},
	};
</script>