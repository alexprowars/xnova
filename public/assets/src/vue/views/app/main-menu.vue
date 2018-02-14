<template>
	<nav class="main-menu">
		<a :class="{active: active}" class="menu-toggle d-sm-none" v-on:click.prevent="$parent.sidebarToggle('menu')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div :class="{active: active}" class="menu-sidebar">
			<ul>
				<li is="main-menu-item" v-for="item in items" :item="item"></li>
			</ul>
		</div>
	</nav>
</template>

<script>
	export default {
		name: "application-main-menu",
		props: ['active'],
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