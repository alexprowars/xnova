<template>
	<li>
		<router-link :class="{active: active}" :to="item.url" :target="(item.new === true ? '_blank' : '')">{{ item.text }}</router-link>
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