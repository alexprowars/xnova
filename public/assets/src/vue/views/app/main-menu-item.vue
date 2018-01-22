<template>
	<li>
		<a v-bind:class="{active: active}" v-bind:href="item.url" v-bind:target="(item.new === true ? '_blank' : '')">{{ item.text }}</a>
	</li>
</template>

<script>
	export default {
		name: "main-menu-item",
		props: ['item'],
		computed: {
			active: function ()
			{
				return this.$root.getMenuActiveLink === this.item.id
			}
		}
	}
</script>