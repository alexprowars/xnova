<template>
	<ul class="menu d-none d-sm-block">
		<li is="main-menu-item" v-for="item in items" v-bind:item="item"></li>
	</ul>
</template>

<script>
	export default {
		name: "main-menu",
		props: ['items', 'active'],
		components: {
			'main-menu-item': require('./main-menu-item.vue')
		}
	}
</script>