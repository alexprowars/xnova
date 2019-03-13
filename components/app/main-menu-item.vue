<template>
	<li>
		<nuxt-link :class="{active: active}" :to="item.url">{{ item.title }}</nuxt-link>
	</li>
</template>

<script>
	export default {
		name: "main-menu-item",
		props: ['item'],
		computed: {
			active () {
				return this.$store.getters.menuActiveLink === this.item.id
			}
		}
	}
</script>