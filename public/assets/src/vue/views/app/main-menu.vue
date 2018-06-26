<template>
	<nav class="main-menu">
		<a :class="{active: active}" class="menu-toggle d-sm-none" @click.prevent="$parent.sidebarToggle('menu')">
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
	import MainMenuItem from './main-menu-item.vue'

	export default {
		name: "main-menu",
		props: ['active'],
		computed: {
			items () {
				return this.$store.state.menu;
			}
		},
		components: {
			MainMenuItem
		}
	}
</script>