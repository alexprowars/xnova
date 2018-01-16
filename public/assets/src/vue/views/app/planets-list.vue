<template>
	<div class="planet-sidebar planetList">
		<div class="list">
			<application-planets-list-row v-for="item in items" v-bind:item="item"></application-planets-list-row>
			<div class="clearfix"></div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "application-planets-list",
		props: ['items'],
		components: {
			'application-planets-list-row': require('./planets-list-row.vue')
		}
	}
</script>

<style scoped>

</style>