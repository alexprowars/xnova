<template>
	<li>
		<a v-bind:class="{active: active}" v-bind:href="item.url" v-bind:target="(item.new === true ? '_blank' : '')" v-on:click="$parent.$parent.sidebarMenu = false">{{ item.text }}</a>
	</li>
</template>

<script>
	export default {
		name: "main-menu-item",
		props: ['item'],
		data: function () { return {} },
		computed: {
			active: function ()
			{
				return this.$parent.active === this.item.id
			}
		}
	}
</script>