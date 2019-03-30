<template>
	<li>
		<nuxt-link :to="item.url">{{ item.title }}</nuxt-link>
	</li>
</template>

<script>
	export default {
		name: "main-menu-item",
		props: {
			item: {
				type: Object
			}
		},
	}
</script>