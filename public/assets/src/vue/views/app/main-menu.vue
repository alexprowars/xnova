<template>
	<ul class="menu d-none d-sm-block">
		<li is="main-menu-item" v-for="item in items" :item="item"></li>
	</ul>
</template>

<script>
	export default {
		name: "application-main-menu",
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