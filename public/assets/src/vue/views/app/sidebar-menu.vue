<template>
	<ul class="nav">
		<li is="main-menu-item" v-for="item in items" v-bind:item="item"></li>
	</ul>
</template>

<script>
	export default {
		name: "sidebar-menu",
		props: ['items', 'active'],
		components: {
			'main-menu-item': require('./main-menu-item.vue')
		}
	}
</script>