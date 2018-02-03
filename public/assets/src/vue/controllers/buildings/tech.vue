<template>
	<div class="page-building page-building-tech">
		<div class="negative">{{ page.message }}</div>
		<div class="block">
			<div class="title">Исследования</div>
			<div class="content page-building-items">
				<div class="row">
					<game-page-buildings-tech-item v-for="item in page.items" :item="item"></game-page-buildings-tech-item>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "tech",
		computed: {
			page () {
				return this.$store.state.page;
			},
		},
		components: {
			'game-page-buildings-tech-item': require('./tech-row.vue')
		},
	}
</script>