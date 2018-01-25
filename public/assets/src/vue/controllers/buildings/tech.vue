<template>
	<div class="page-building page-building-unit">
		<div class="negative">{{ page.message }}</div>
		<div class="block">
			<div class="title">Исследования</div>
			<div class="content">
				<div class="row research">
					<game-page-buildings-tech-item v-for="item in page.items" v-bind:item="item"></game-page-buildings-tech-item>
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