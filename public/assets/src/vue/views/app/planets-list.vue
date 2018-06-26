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
				<planet-row v-for="item in items" :key="item['id']" :item="item"></planet-row>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</template>

<script>
	import PlanetRow from './planets-list-row.vue'

	export default {
		name: "planets-list",
		props: ['active'],
		computed: {
			items () {
				return this.$store.state.user.planets;
			}
		},
		components: {
			PlanetRow
		}
	}
</script>