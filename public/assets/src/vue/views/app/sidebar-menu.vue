<template>
	<ul class="nav">
		<li is="main-menu-item" v-for="item in items" :item="item"></li>
	</ul>
</template>

<script>
	export default {
		name: "sidebar-menu",
		computed: {
			items () {
				return this.$store.state.menu;
			}
		},
		components: {
			'main-menu-item': require('./main-menu-item.vue')
		}
	}
</script>