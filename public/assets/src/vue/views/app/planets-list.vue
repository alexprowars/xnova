<template>
	<div class="main-planets">
		<a :class="{active: active}" class="planet-toggle d-sm-none" @click.prevent="$parent.sidebarToggle('planet')"><span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>
		<div :class="{active: active}" class="planet-sidebar">
			<div class="list">
				<application-planets-list-row v-for="item in items" :item="item"></application-planets-list-row>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "application-planets-list",
		props: ['active'],
		computed: {
			items () {
				return this.$store.state.user.planets;
			}
		},
		components: {
			'application-planets-list-row': require('./planets-list-row.vue')
		}
	}
</script>