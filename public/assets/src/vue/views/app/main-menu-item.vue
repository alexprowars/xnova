<template>
	<li>
		<a :class="{active: active}" :href="item.url" :target="(item.new === true ? '_blank' : '')">{{ item.text }}</a>
	</li>
</template>

<script>
	export default {
		name: "main-menu-item",
		props: ['item'],
		computed: {
			active: function () {
				return this.$store.getters.menuActiveLink === this.item.id
			}
		}
	}
</script>